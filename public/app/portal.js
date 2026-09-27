import { api, config, refreshCsrf } from './api.js';
import { authentication } from './auth.js';
import * as requests from './requests.js';
import * as workspace from './workspace.js';
import { esc, icon, empty, link } from './ui.js';

const app=document.querySelector('#app');
let version=0;
const context={user:null,catalogs:null,render,go(route){if(location.hash===`#${route}`)render();else location.hash=route;}};
function shell(route) {
    const staff=context.user.role!=='requestor',admin=context.user.role==='admin';
    const navigation=[['dashboard','Dashboard'],['new','New request'],['requests',staff?'Requests':'My requests'],...(staff?[['storage','Drawer directory'],['history','History log']]:[]),...(admin?[['users','User management'],['catalogs/document_types','Catalogs'],['feedback-records','Feedback records']]:[]),['profile','Profile'],['help','Help']];
    return `<div class="shell"><aside class="sidebar" id="sidebar"><div class="brand"><img src="${esc(config.logo)}" alt="CAE emblem"><div><strong>CDTrack</strong><span>CAE Dean’s Office</span></div></div><p class="portal-name">${admin?'Administrator':staff?'Counter staff':'Requestor'} portal</p><nav class="nav" aria-label="Main navigation">${navigation.map(([path,label])=>`<a href="#${path}" ${route===path.split('/')[0]?'class="active" aria-current="page"':''}>${icon(path)}${label}</a>`).join('')}</nav><div class="sidebar-footer"><button id="logout" class="btn full">${icon('logout')}Sign out</button></div></aside><div class="workarea"><header class="topbar"><button id="menu" class="btn mobile-menu" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="false">${icon('menu')}</button><span class="breadcrumb">CAE / Document tracking</span><a href="#profile" class="account-name">${esc(context.user.name)}</a></header><main id="main" class="main" tabindex="-1"><p role="status">Loading…</p></main></div></div>`;
}
async function render() {
    const current=++version;
    const [path,query='']=location.hash.slice(1).split('?');
    const [route= 'dashboard',id]= (path|| (config.resetToken?'reset':'dashboard')).split('/');
    const params=new URLSearchParams(query);
    if(!context.user){const view=authentication(['register','forgot','reset'].includes(route)?route:'login',async user=>{context.user=user;context.go('dashboard');});app.innerHTML=view.html;view.bind?.();return;}
    app.innerHTML=shell(route);
    document.querySelector('#menu').onclick=event=>{const open=document.querySelector('#sidebar').classList.toggle('open');event.currentTarget.setAttribute('aria-expanded',String(open));};
    document.querySelector('#logout').onclick=async event=>{event.currentTarget.disabled=true;try{await api('logout',{method:'POST'});context.user=null;await refreshCsrf();context.go('login');}catch(error){event.currentTarget.disabled=false;window.alert(error.message);}};
    try {
        context.catalogs=await api('catalogs');
        if(current!==version)return;
        if(['users','catalogs','feedback-records'].includes(route)&&context.user.role!=='admin')throw new Error('This page is available to administrators.');
        if(['storage','history','collection'].includes(route)&&context.user.role==='requestor')throw new Error('This page is available to counter staff.');
        const routes={dashboard:()=>workspace.dashboard(context),requests:()=>requests.requestList(context,params),new:()=>requests.newRequest(context),detail:()=>requests.detail(context,id),collection:()=>requests.collection(context,id),feedback:()=>requests.feedback(context,id),stub:()=>requests.stub(context,id),profile:()=>workspace.profile(context),storage:()=>workspace.storage(context),history:()=>workspace.history(context,params),users:()=>workspace.users(context,params),catalogs:()=>workspace.catalogs(context,id),'feedback-records':()=>workspace.history(context,params,true),help:workspace.help};
        const view=await (routes[route]||routes.dashboard)();
        if(current!==version)return;
        document.querySelector('#main').innerHTML=view.html;view.bind?.();document.title=`CAE CDTrack · ${document.querySelector('h1')?.textContent||'Portal'}`;
    }catch(error){if(current!==version)return;const main=document.querySelector('#main');if(main)main.innerHTML=empty('Unable to open this page',error.message,link('Back to dashboard','dashboard'));}
}
window.addEventListener('hashchange',render);
window.addEventListener('session-expired',()=>{if(context.user){context.user=null;context.go('login');}});
try{await refreshCsrf();context.user=await api('me');}catch(error){if(error.status!==401){app.innerHTML=empty('Unable to connect',error.message,'<button class="btn" onclick="location.reload()">Try again</button>');throw error;}}
await render();

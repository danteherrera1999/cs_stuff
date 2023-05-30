var Ibox = document.getElementById("inv-box");
var Tbox = document.getElementById("trade-box");
var TradeButton = document.getElementById("trade-button");
var invData;
var input_field = document.getElementById("search-input-box");
var search_box = document.getElementById("search-box");
var for_box = document.getElementById("for-box");
const item_data_url = './db.json';
const cdn_url = 'https://steamcommunity-a.akamaihd.net/economy/image/';
const market_url_prefix = "https://steamcommunity.com/market/listings/730/";
const SID = document.body.id;
const inv_url = "./inv.json";
//const inv_url = `https://steamcommunity.com/inventory/${SID}/730/2?l=english&count=5000`
const SIDW = null;
const send_url = 'https://3rs9e5kpze.execute-api.us-east-2.amazonaws.com/test/trades';
var names;
var classids;
var icons;
IDS = [];

function find_asset(AID){
    let asset_found;
    invData['assets'].forEach((asset)=>{
        if (asset['assetid'] == AID){
            asset_found = asset;
        }
    })
    return asset_found;
}
function find_description(CID){
    let description_found;
    invData['descriptions'].forEach((desc)=>{
        if (desc['classid'] == CID){
            description_found = desc;
        }
    })
    return description_found;
}
function post_trade(){
    DES = [];
    AIDS = [];
    CIDS = [];
    Tbox.childNodes.forEach((CN)=>{
        [AID,CID] = CN.id.replace("_dummy","").split("||")
        AIDS.push(AID);
    })
    for_box.childNodes.forEach((CN)=>{
        CIDS.push(CN.id.replace("_dummy",""));
    })
    TRADE = JSON.stringify({"HAVE":AIDS,"WANT":CIDS});
    window.location.replace(`post_trade.php?trade_data=${TRADE}`);
}
function item_click(e){
    elem = document.getElementById(e.target.id);
    if (e.target.tagName != "A" && Tbox.childNodes.length < 10){
        CL = elem.classList;
        if (!(CL.contains('clicked'))){
            if (elem.parentElement == Ibox){
                elem_dummy = elem.cloneNode(true);
                elem_dummy.id = elem.id + '_dummy';
                elem_dummy.addEventListener("click",function(e){
                    if (e.target.tagName != "A"){
                        elem = document.getElementById(this.id.replace('_dummy',''));
                        elem.classList.remove('clicked');
                        elem.style.backgroundColor = 'rgb(41, 41, 41)';
                        this.remove();
                    }
                })
                Tbox.appendChild(elem_dummy);
                CL.add("clicked");
                elem.style.backgroundColor = 'rgba(50,50,50,.7)';
            }
        }
    }
}
function search_item_click(e){
    elem = document.getElementById(e.target.id);
    try {CL = elem.classList;}
    catch{}
    if (!(CL.contains('clicked')) && for_box.childNodes.length < 10){
        elem_dummy = elem.cloneNode(true);
        elem_dummy.id = elem.id + '_dummy';
        elem_dummy.addEventListener("click",function(e){
            try{
                elem = document.getElementById(this.id.replace('_dummy',''));
                elem.classList.remove('clicked');
                elem.style.backgroundColor = 'rgb(41, 41, 41)';
            }
            catch{};
            this.remove();
        })
        for_box.appendChild(elem_dummy);
        CL.add("clicked");
        elem.style.backgroundColor = 'rgba(255,255,255,.2)';
    }
}
function create_search_item(item,current_box_ids){
    var cell = document.createElement("div");
    icon_url = cdn_url + item[2];
    cell.className = "search-item";
    cell.id = item[1];
    cell.style.backgroundImage = `url(${icon_url})`;
    cell.innerHTML = item[0];
    if (current_box_ids.includes(cell.id)){
        cell.classList.add("clicked");
        cell.style.backgroundColor = 'rgba(255,255,255,.2)';
    }
    search_box.appendChild(cell);
    cell.addEventListener("click",search_item_click);
}
function item_search(){
    current_box_ids = [...for_box.childNodes].map((node) => node.id.replace("_dummy",""));
    while (search_box.firstChild) {
        search_box.removeChild(search_box.firstChild);
    }
    let Qs = input_field.value.trim().split(" ");
    let item_matches = [];
    for (var i = 0; i<names.length;i++){
        if (Qs.every((Q) => names[i].includes(Q)) && item_matches.length<10){
            item_matches.push([names[i],classids[i],icons[i]]);
        }
    }
    item_matches.forEach((item_match)=>{
        create_search_item(item_match,current_box_ids);
    })
}
async function generate_wildcards(){
    wildcards = document.getElementById("wildcard-box").childNodes;
    wildcards.forEach((wc)=>{
        wc.addEventListener("click",search_item_click);
    })
}
async function load_itemdb(){
    const response = await fetch(item_data_url);
    const res = await response.json();
    [names,classids,icons] = [res['names'],res['classids'],res['icons']];
}
async function load_data(){
    const response = await fetch(inv_url);
    const res = await response.json();
    invData = res;
    let descriptions = res['descriptions'];
    const assets = res['assets'];
    let d2 = [];
    let aids = [];
    let acids = [];
    assets.forEach(asset=>{
        acids.push(asset['classid']);
        aids.push(asset['assetid']);
        descriptions.forEach(description=>{
            if (description['classid'] == asset['classid']){
                d2.push(description);
            }
        })
    });
    const items = Object.keys(d2);
    for (let i  = 0; i< items.length;i++){
        item = d2[items[i]]
        tradable = item['tradable'];
        item_name = item['name'];
        icon_url = cdn_url + item['icon_url'];
        var cell = document.createElement("div");
        cell.className = "inv-item";
        cell.style.backgroundImage = `url(${icon_url})`;
        var inspect_anchor = document.createElement("a");
        inspect_anchor.className = "inspect_anchor";
        var market_anchor = document.createElement("a");
        market_anchor.className = "market_anchor";
        market_anchor.innerHTML = "Market";
        market_anchor.href= market_url_prefix + item['market_name'];
        market_anchor.target ="_blank";
        cell.innerHTML = item_name;
        try{
            if (item['classid'] == acids[0]){
                CID = acids.shift();
                AID = aids.shift();
                cell.id = `${AID}||${CID}`;
                inspect_link = item['actions'][0]['link'].replace("%owner_steamid%",SID).replace("%assetid%",AID)
                inspect_anchor.href = inspect_link;
                inspect_anchor.innerHTML = "Inspect";
                cell.appendChild(inspect_anchor);
                IDS.push(cell.id);
            }
        }
        catch{}
        cell.appendChild(market_anchor);
        if (tradable){
            cell.addEventListener("click",item_click);
            Ibox.appendChild(cell);
        }
    }
}

let init = () => {Promise.all([load_itemdb(),generate_wildcards(),load_data()])};

init();
TradeButton.addEventListener("click",post_trade)
input_field.addEventListener("change",item_search);
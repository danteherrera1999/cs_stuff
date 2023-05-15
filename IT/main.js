var Ibox = document.getElementById("inv-box");
var Tbox = document.getElementById("trade-box");
var TradeButton = document.getElementById("trade-button");
var invData;
const url = './data.json';
const cdn_url = 'https://steamcommunity-a.akamaihd.net/economy/image/';
const market_url_prefix = "https://steamcommunity.com/market/listings/730/";
const SID = "76561198069197135";
const SIDW = null;
const send_url = 'https://3rs9e5kpze.execute-api.us-east-2.amazonaws.com/test/trades';
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
    ASS = [];
    ASSW = [];
    DESW = [];
    Tbox.childNodes.forEach((CN)=>{
        [AID,CID] = CN.id.replace("_dummy","").split("||")
        ASS.push(find_asset(AID));
        DES.push(find_description(CID));
    })
    TRADE = JSON.stringify({"HAVE":{'assets': ASS, "descriptions": DES,"SID": SID},"WANT":{}})
    HAVE = {'assets': ASS, "descriptions": DES,"SID": SID};
    WANT = {'assets': ASSW, "descriptions": DESW,"SID": SIDW};
    //TRADE = {"HAVE":HAVE,"WANT":WANT,"UTS":Date.now()};
    //TRADE = HAVE;
    console.log(TRADE);
}
function item_click(e){
    elem = document.getElementById(e.target.id);
    if (e.target.tagName != "A"){
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
async function load_data(){
    const response = await fetch(url);
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


load_data();
TradeButton.addEventListener("click",post_trade)
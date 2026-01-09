using System;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Modules.BnIdLinker;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.LinkBnIdDialog.Domain;
using GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Presenters
{
    public class LinkBnIdWebViewDialogPresenter : ILinkBnIdWebViewDialogViewDelegate
    {
        [Inject] BnIdLinker BnIdLinker { get; }
        [Inject] LinkBnIdWebViewDialogViewController ViewController { get; }

        void ILinkBnIdWebViewDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.LoadURL(BnIdLinker.GetBnIdLink());
        }

        void ILinkBnIdWebViewDialogViewDelegate.OnWebViewHooked(string msg)
        {
            if (!CheckBnIdLink(msg)) return;

            var bnIdCode = GetBnIdCode(msg);
            ViewController.OnRedirected?.Invoke(bnIdCode);
            ViewController.Close();
        }

        bool CheckBnIdLink(string url)
        {
            return url.Contains("jumble-rush://");
        }

        BnIdCode GetBnIdCode(string url)
        {
            var uri = new Uri(url);
            var queryStr = uri.GetComponents(UriComponents.Query, UriFormat.SafeUnescaped);

            var queries = queryStr
                .Split('&')
                .Select(x => x.Split('='))
                .Where(x => x.Length == 2)
                .ToDictionary(x => x[0], x => x[1]);

            if (!queries.ContainsKey("code")) return BnIdCode.Empty;

            var code = queries["code"];
            return new BnIdCode(code);
        }
    }
}

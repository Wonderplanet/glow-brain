using System.Collections.Generic;
using GLOW.Scenes.HomeHelpDialog.Domain.AssetLoaders;
using GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects;
using GLOW.Scenes.HomeHelpDialog.Presentation.Misc;
using GLOW.Scenes.HomeHelpDialog.Presentation.Translators;
using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;
using GLOW.Scenes.HomeHelpDialog.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Presenters
{
    public class HomeHelpDialogPresenter : IHomeHelpDialogViewDelegate
    {
        [Inject] HomeHelpDialogViewController ViewController { get; }
        [Inject] IHomeHelpInfoListAssetLoader HomeHelpInfoListAssetLoader { get; }

        void IHomeHelpDialogViewDelegate.ViewWillAppear()
        {
            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                var info = await HomeHelpInfoListAssetLoader.Load(cancellationToken, HomeHelpInfoAssetKey.Default);
                var viewModel = HomeHelpViewModelTranslator.Translate(info);
                ViewController.SetUp(viewModel);
            });
        }

        void IHomeHelpDialogViewDelegate.OnClose()
        {
            HomeHelpInfoListAssetLoader.Unload(HomeHelpInfoAssetKey.Default);
            ViewController.Dismiss();
        }
    }
}

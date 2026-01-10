using GLOW.Scenes.ArtworkExpandDialog.Domain.Evaluator;
using GLOW.Scenes.ArtworkExpandDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ValueObject;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ViewModels;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.ArtworkExpandDialog.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-4_作品別原画表示
    /// 　　91-4-1_原画拡大ダイアログ
    /// </summary>
    public class ArtworkExpandDialogPresenter : IArtworkExpandDialogViewDelegate
    {
        [Inject] ArtworkExpandDialogViewController ViewController { get; }
        [Inject] ArtworkExpandDialogViewController.Argument Argument { get; }
        [Inject] GetArtworkExpandUseCase GetArtworkExpandUseCase { get; }
        [Inject] HasArtworkEvaluator HasArtworkEvaluator { get; }

        void IArtworkExpandDialogViewDelegate.OnViewDidLoad()
        {
            var model = GetArtworkExpandUseCase.GetArtwork(Argument.MstArtworkId);
            var viewModel = new ArtworkExpandDialogViewModel(model.Name, model.Description, model.AssetPath);

            switch (Argument.ArtworkDetailDisplayType)
            {
                case ArtworkDetailDisplayType.Normal:
                    ViewController.SetUpFromEncyclopedia(viewModel);
                    break;
                case ArtworkDetailDisplayType.GrayOut:
                    bool isLock = !HasArtworkEvaluator.HasArtwork(Argument.MstArtworkId);
                    ViewController.SetUpFromExchangeShop(viewModel, isLock);
                    break;
            }
        }

        void IArtworkExpandDialogViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}

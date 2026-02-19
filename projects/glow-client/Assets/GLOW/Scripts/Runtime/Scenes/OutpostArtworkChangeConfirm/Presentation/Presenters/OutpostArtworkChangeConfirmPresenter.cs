using GLOW.Scenes.OutpostArtworkChangeConfirm.Domain.UseCases;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.ViewModels;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.Presenters
{
    public class OutpostArtworkChangeConfirmPresenter : IOutpostArtworkChangeConfirmViewDelegate
    {
        [Inject] OutpostArtworkChangeConfirmViewController ViewController { get; }
        [Inject] OutpostArtworkChangeConfirmViewController.Argument Argument { get; }
        [Inject] GetOutpostArtworkChangeConfirmUseCase GetOutpostArtworkChangeConfirmUseCase { get; }
        [Inject] ChangeOutpostArtworkUseCase ChangeOutpostArtworkUseCase { get; }

        void IOutpostArtworkChangeConfirmViewDelegate.OnViewDidLoad()
        {
            var model = GetOutpostArtworkChangeConfirmUseCase.GetChangeArtworkPath(Argument.MstArtworkId);
            var viewModel = new OutpostArtworkChangeConfirmViewModel(
                model.BeforeArtworkSmallPath,
                model.AfterArtworkSmallPath
            );

            ViewController.Setup(viewModel);
        }

        void IOutpostArtworkChangeConfirmViewDelegate.OnChangeArtworkButtonTapped()
        {
            ChangeOutpostArtworkUseCase.ChangeOutpostArtwork(Argument.MstArtworkId);
            ViewController.Dismiss();
        }

        void IOutpostArtworkChangeConfirmViewDelegate.OnChancelButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}

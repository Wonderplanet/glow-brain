using GLOW.Scenes.PvpInfo.Domain;
using GLOW.Scenes.PvpInfo.Domain.UseCase;
using GLOW.Scenes.PvpInfo.Presentation.Translator;
using GLOW.Scenes.PvpInfo.Presentation.View;
using Zenject;

namespace GLOW.Scenes.PvpInfo.Presentation.Presenter
{
    public class PvpInfoPresenter : IPvpInfoViewDelegate
    {
        [Inject] PvpInfoViewController ViewController { get; }
        [Inject] PvpInfoViewController.Argument Argument { get; }
        [Inject] PvpInfoUseCase UseCase { get; }

        void IPvpInfoViewDelegate.OnViewDidLoad()
        {
            var useCaseModel = UseCase.GetModel(Argument.SysPvpSeasonId);
            var viewModel = PvpInfoViewModelTranslator.Translate(useCaseModel);
            ViewController.Setup(viewModel);
        }

        void IPvpInfoViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}

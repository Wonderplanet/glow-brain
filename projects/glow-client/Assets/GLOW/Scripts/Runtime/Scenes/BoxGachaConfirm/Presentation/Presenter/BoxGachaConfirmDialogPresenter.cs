using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.BoxGachaConfirm.Domain.UseCase;
using GLOW.Scenes.BoxGachaConfirm.Presentation.Translator;
using GLOW.Scenes.BoxGachaConfirm.Presentation.View;
using Zenject;

namespace GLOW.Scenes.BoxGachaConfirm.Presentation.Presenter
{
    public class BoxGachaConfirmDialogPresenter : IBoxGachaConfirmDialogViewDelegate
    {
        [Inject] BoxGachaConfirmDialogViewController ViewController { get; }
        [Inject] BoxGachaConfirmDialogViewController.Argument Argument { get; }
        [Inject] ShowBoxGachaConfirmUseCase ShowBoxGachaConfirmUseCase { get; }
        
        void IBoxGachaConfirmDialogViewDelegate.OnViewDidLoad()
        {
            var model = ShowBoxGachaConfirmUseCase.ShowDrawConfirm(Argument.MstBoxGachaId);
            var viewModel = BoxGachaConfirmDialogViewModelTranslator.ToViewModel(model);
            ViewController.SetUpConfirmDialog(viewModel);
        }

        void IBoxGachaConfirmDialogViewDelegate.OnDrawButtonTapped(GachaDrawCount drawCount)
        {
            // ダイアログを閉じてからコールバックを実行する
            ViewController.Dismiss(completion: () =>
            {
                ViewController.OnDrawButtonTappedAction?.Invoke(drawCount);
            });
        }

        void IBoxGachaConfirmDialogViewDelegate.OnCancelButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}
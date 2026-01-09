using GLOW.Scenes.EncyclopediaEffectDialog.Domain.UseCases;
using GLOW.Scenes.EncyclopediaEffectDialog.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaEffectDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEffectDialog.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-3_キャラ図鑑ランク
    /// 　　　91-1-3-2_発動中の図鑑効果ダイアログ
    /// </summary>
    public class EncyclopediaEffectDialogPresenter : IEncyclopediaEffectDialogViewDelegate
    {
        [Inject] EncyclopediaEffectDialogViewController ViewController { get; }
        [Inject] GetEncyclopediaEffectUseCase GetEncyclopediaEffectUseCase { get; }

        void IEncyclopediaEffectDialogViewDelegate.OnViewDidLoad()
        {
            var model = GetEncyclopediaEffectUseCase.GetEffects();
            var viewModel = new EncyclopediaEffectDialogViewModel(model.AttackPower, model.Hp, model.Heal);
            ViewController.Setup(viewModel);
        }

        void IEncyclopediaEffectDialogViewDelegate.OnBackButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}

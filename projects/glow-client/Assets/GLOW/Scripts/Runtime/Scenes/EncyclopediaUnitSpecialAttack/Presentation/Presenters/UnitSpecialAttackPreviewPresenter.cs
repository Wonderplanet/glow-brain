using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Domain.UseCases;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Translators;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-1_ヒーローキャラ表示
    /// 　　　91-3-1-1_必殺ワザ再生
    /// </summary>
    public class UnitSpecialAttackPreviewPresenter : IUnitSpecialAttackPreviewViewDelegate
    {
        [Inject] UnitSpecialAttackPreviewViewController ViewController { get; }
        [Inject] UnitSpecialAttackPreviewViewController.Argument Argument { get; }
        [Inject] GetUnitSpecialAttackPreviewUseCase GetUnitSpecialAttackPreviewUseCase { get; }

        void IUnitSpecialAttackPreviewViewDelegate.OnViewDidLoad()
        {
            var model = GetUnitSpecialAttackPreviewUseCase.GetUnitSpecialAttack(Argument.MstUnitId);
            var viewModel = UnitSpecialAttackPreviewViewModelTranslator.Translate(model);
            ViewController.Setup(viewModel);
        }

        void IUnitSpecialAttackPreviewViewDelegate.OnEndAnimation()
        {
            ViewController.Dismiss();
        }
    }
}

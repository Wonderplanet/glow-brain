using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattle.Domain.UseCase;
using GLOW.Scenes.AdventBattle.Presentation.View.AdventBattleInfo;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.InGameSpecialRule.Presentation.Translators;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Presentation.Presenter
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-2_降臨バトル詳細情報表示
    ///
    /// 44_降臨バトル
    /// 　44-6_特別ルール
    /// 　　44-6-2-1_特別ルール専用ダイアログ（リミテッドバトルを参考）
    /// </summary>
    public class AdventBattleInfoPresenter : IAdventBattleInfoDelegate
    {
        [Inject] AdventBattleInfoViewController ViewController { get; }
        [Inject] AdventBattleInfoViewController.Argument Argument { get; }
        [Inject] AdventBattleInfoUseCase AdventBattleInfoUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }

        public void OnViewWillAppear()
        {
            var model = AdventBattleInfoUseCase.GetAdventBattleInfoModel(Argument.MstAdventBattleId);
            var enemyViewModels = HomeStageInfoEnemyCharacterViewModelTranslator
                .ToHomeStageInfoEnemyCharacterViewModel(model.EnemyList);
            var rewardViewModels = PlayerResourceIconViewModelTranslator
                .ToPlayerResourceIconViewModels(model.RewardList, true);
            ViewController.SetupAdventBattleInfoView(enemyViewModels, rewardViewModels, model.InGameDescription);

            var inGameSpecialRuleViewModel = InGameSpecialRuleViewModelTranslator.TranslateInGameSpecialRuleViewModel(
                model.InGameSpecialRuleModel,
                InGameSpecialRuleFromUnitSelectFlag.False);
            ViewController.SetupInGameSpecialRule(inGameSpecialRuleViewModel);
        }

        public void OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        public void OnTappedPlayerResourceIcon(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }
    }
}

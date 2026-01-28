using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.TutorialTipDialog.Domain.Models;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using GLOW.Scenes.UnitList.Presentation.Views;
using GLOW.Scenes.UnitTab.Presentation.Views;
using Zenject;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    public class MainPart2TutorialSequence : BaseTutorialSequence
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            
            await FadeInGrayOut(cancellationToken);
            
            // part2
            var tipModels = GetTutorialTips(new MasterDataId("Main2"));
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;

            if (tutorialStatus.IsStartMainPart2())
            {
                await EnhanceTutorial(cancellationToken, tipModels);
            }
            
            ShowIndicator("home_main_start_button");
            await WaitClickEvent(cancellationToken, "home_main_start_button");
            HideIndicator();

            // メインパート2終了
            await FadeOutGrayOut(cancellationToken);
        }

        async UniTask EnhanceTutorial(CancellationToken cancellationToken, IReadOnlyList<TutorialTipModel> tipModels)
        {
            // 強化へ誘導
            await ShowTutorialText(cancellationToken, "「キャラ」を強化しよう！", 0f);
            await HideTutorialText(cancellationToken);
            ShowIndicator("home_footer_unit_button");
            await WaitClickEvent(cancellationToken, "home_footer_unit_button");
            HideIndicator();

            // キャラ一覧画面
            await WaitViewPresentation<UnitTabViewController>(cancellationToken);
         
            ShowIndicator("unit_list_unit_icon",
                t => t.GetComponent<UnitListCellComponent>().UserUnitId == GetEnhanceTargetUnit().UsrUnitId);
            await WaitClickEvent(cancellationToken, "unit_list_unit_icon",
                t => t.GetComponent<UnitListCellComponent>().UserUnitId == GetEnhanceTargetUnit().UsrUnitId);
            HideIndicator();

            // キャラ強化画面
            await WaitViewPresentation<UnitViewController>(cancellationToken);
            ShowIndicator("unit_enhance_level_up_button");
            await WaitClickEvent(cancellationToken, "unit_enhance_level_up_button");
            HideIndicator();
            HideGrayOut();

            // キャラ強化ダイアログ画面
            await WaitViewPresentation<UnitLevelUpDialogViewController>(cancellationToken);
            ShowOverlayGrayOut();

            HighlightTarget("unit_level_up_dialog_enhance_cost");
            await ShowTutorialText(cancellationToken, "強化にはコインが必要だよ", -200);
            await HideTutorialText(cancellationToken);
            UnHighlightTarget();
            
            ShowIndicator("unit_level_up_dialog_enhance_button");
            await WaitClickEvent(cancellationToken, "unit_level_up_dialog_enhance_button");
            HideIndicator();
            
            HideOverlayGrayOut();
            
            // 通信待ち状態(通信終了でダイアログが閉じるのでそこまで待つ)
            // チュートリアル進行状況は強化APIで更新される
            await WaitDismissModal<UnitLevelUpDialogViewController>(cancellationToken);
            // 演出の待機
            const float unitLevelUpDialogWaitSecond = 1.0f;
            await DelayWithInteractionDisable<UnitEnhanceView>(unitLevelUpDialogWaitSecond, cancellationToken);

            // ダイアログ表示
            ShowTutorialDialog(tipModels[0].Title, tipModels[0].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
            
            // キャラ強化画面
            await FadeInGrayOut(cancellationToken);
            await ShowTutorialText(cancellationToken, "次のバトルに挑戦しよう！", 0);
            await HideTutorialText(cancellationToken);

            ShowIndicator("unit_enhance_back_button");
            await WaitClickEvent(cancellationToken, "unit_enhance_back_button");
            HideIndicator();

            // キャラ一覧画面
            await WaitViewPresentation<UnitTabViewController>(cancellationToken);
            ShowIndicator("home_footer_main_button");
            await WaitClickEvent(cancellationToken, "home_footer_main_button");
            HideIndicator();

            // ホーム画面
            await WaitViewPresentation<HomeMainViewController>(cancellationToken);
            // 早すぎるので少し待つ
            await Delay(0.3f, cancellationToken);
        }

        // 強化対象キャラの取得
        UserUnitModel GetEnhanceTargetUnit()
        {
            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;

            foreach (var model in userUnitModels)
            {
                var mstUnit = MstCharacterDataRepository.GetCharacter(model.MstUnitId);
                if(mstUnit.Rarity ==  Rarity.UR)
                {
                    return model;
                }
            }
            
            var userUnitModel = userUnitModels.OrderByDescending(u => u.Rank.Value)
                .DefaultIfEmpty(UserUnitModel.Empty)
                .FirstOrDefault();
            return userUnitModel;
        }
    }
}

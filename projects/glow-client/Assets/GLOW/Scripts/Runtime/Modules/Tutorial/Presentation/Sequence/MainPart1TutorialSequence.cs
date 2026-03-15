using System.Collections.Generic;
using System.Threading;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.TutorialTipDialog.Domain.Models;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.GachaResult.Presentation.Views;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView;
using GLOW.Scenes.MainQuestTop.Presentation;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using GLOW.Scenes.UnitList.Presentation.Views;
using GLOW.Scenes.UnitTab.Presentation.Views;
using UnityEngine;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    public class MainPart1TutorialSequence : BaseTutorialSequence
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] TutorialPartyFormationUseCase TutorialPartyFormationUseCase { get; }
        [Inject] ApplyPartyFormationUseCase ApplyPartyFormationUseCase { get; }
        [Inject] TutorialGachaConfirmedApplyUseCase TutorialGachaConfirmedApplyUseCase { get; }
        [Inject] TutorialApplyPartyFormationUseCase TutorialApplyPartyFormationUseCase { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public override async UniTask Play(CancellationToken cancellationToken)
        {
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            var tipModels = GetTutorialTips(new MasterDataId("Main1"));
            
            // シーケンス開始
            await FadeInGrayOut(cancellationToken);

            // ガシャチュートリアル
            if (tutorialStatus.IsStartMainPart1())
            {
                await GachaDrawTutorial(cancellationToken);
                // 更新されているので再取得
                tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            }
            
            // 中断復帰でキャラ強化済みの場合、キャラ一覧表示画面にしておく
            if (tutorialStatus.IsUnitEnhanced())
            {
                await NavigateToUnitTab(cancellationToken);
            }

            // キャラ強化チュートリアル
            if (tutorialStatus.IsGachaConfirmed())
            {
                await UnitEnhanceTutorial(cancellationToken, tipModels);
                // 更新されているので再取得
                tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            }
            
            // キャラ編成チュートリアル
            if (tutorialStatus.IsUnitEnhanced())
            {
                await PartyFormationTutorial(cancellationToken);
            }
            
            // ステージ詳細チュートリアル チュートリアル完了
            await StageDescriptionTutorial(cancellationToken);

            // 終了
            await FadeOutGrayOut(cancellationToken);
        }
        

        async UniTask GachaDrawTutorial(CancellationToken cancellationToken)
        {
            // ホーム画面
            await ShowTutorialText(cancellationToken, "新しい仲間を集めよう！", 0f);
            await HideTutorialText(cancellationToken);

            ShowIndicator("home_footer_gacha_button");
            await WaitClickEvent(cancellationToken, "home_footer_gacha_button");
            HideIndicator();
            await WaitViewPresentation<GachaListViewController>(cancellationToken);

            await ShowTutorialText(cancellationToken, "「キャラ」を仲間にするには\n「プリズム」が必要だよ", 0f);
            await ShowTutorialText(cancellationToken, "今回は特別に、\nプリズムを使わなくてもガシャが引けるよ！\n", 0f);
            await HideTutorialText(cancellationToken);

            // ヘッダーのタップを無効化しておく
            DisableHomeHeaderTap();

            // ガシャ一覧
            // チュートリアルガシャボタンをハイライト
            HighlightTarget("tutorial_content_area");

            // チュートリアルガシャの「ひく」ボタンを強調アニメーション
            var button = FindTargetObject("tutorial_draw_button")?.GetComponent<TutorialGachaButtonComponent>();
            if(button != null)
            {
                button.SetButtonEffectActive(true);
            }

            await WaitClickEvent(cancellationToken, "tutorial_draw_button");
            HideIndicator();

            // ガシャ演出のためにグレーアウトを解除
            HideGrayOut();

            // ガシャ結果画面の表示を待つ
            await WaitViewPresentation<GachaResultViewController>(cancellationToken);

            // チュートリアルガシャ結果を確定させるのを待つ
            await UniTask.WaitUntil(
                () => GameRepository.GetGameFetchOther().TutorialStatus.IsGachaConfirmed(),
                cancellationToken: cancellationToken);

            // ガシャ確定後にガシャ一覧に戻るまで待機
            await WaitViewPresentation<GachaListViewController>(cancellationToken);
            await FadeInGrayOut(cancellationToken);

            // ヘッダーのタップを有効化する
            EnableHomeHeaderTap();
        }
        
        async UniTask NavigateToUnitTab(CancellationToken cancellationToken)
        {
            // キャラ一覧へ誘導
            ShowIndicator("home_footer_unit_button");
            await WaitClickEvent(cancellationToken, "home_footer_unit_button");
            HideIndicator();
            await WaitViewPresentation<UnitTabViewController>(cancellationToken);
        }

        async UniTask UnitEnhanceTutorial(
            CancellationToken cancellationToken, 
            IReadOnlyList<TutorialTipModel> tipModels)
        {
            // NOTE:ガシャ画面から開始、中断復帰の場合にホーム開始となるがフッタータップから開始のため問題なし
            
            // ガシャ結果の確定後の反映が成功しているか確認する(編成・アバター)
            await UpdateGachaConfirmedApplyIfNeeds(cancellationToken);
            
            await ShowTutorialText(cancellationToken, "仲間になった「キャラ」を強化しよう！", 0f);
            await HideTutorialText(cancellationToken);
            
            // キャラ一覧へ誘導
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
            await ShowTutorialText(cancellationToken, "次はキャラを編成しよう！", 0);
            await HideTutorialText(cancellationToken);

            // キャラ一覧画面へ
            ShowIndicator("unit_enhance_back_button");
            await WaitClickEvent(cancellationToken, "unit_enhance_back_button");
            HideIndicator();
        }
        
        async UniTask PartyFormationTutorial(CancellationToken cancellationToken)
        {
            // エラーで編成は完了し、チュートリアルステータスの更新のみ失敗している場合はステータス更新だけする
            if (IsPartyFormationApplied())
            {
                await ProgressTutorialStatus(cancellationToken);
                return;
            }

            // キャラ一覧
            await WaitViewPresentation<UnitTabViewController>(cancellationToken);
            ShowIndicator("party_formation_button");
            await WaitClickEvent(cancellationToken, "party_formation_button");
            HideIndicator();

            // パーティ編成
            await UniTask.Delay(300, cancellationToken: cancellationToken);
            await ShowTutorialText(cancellationToken, "キャラアイコンをタップして\nパーティに入れよう！", 0f);
            await HideTutorialText(cancellationToken);

            // 編成済みのURを除いて最大で4体編成に加える
            await AddParty(cancellationToken);

            // パーティ編成を保存 チュートリアル再開を待つためチュートリアル内で行う
            ScreenInteractionControl.ActivityBegin();
            await ApplyPartyFormation(cancellationToken);

            // パーティ編成完了のステータス更新
            await ProgressTutorialStatus(cancellationToken);
            ScreenInteractionControl.ActivityEnd();

            HighlightTarget("party_formation_party_list");
            await ShowTutorialText(cancellationToken, "パーティを組めたね！", 0f);
            await ShowTutorialText(cancellationToken, "最初のクエストを確認しよう！", 0f);
            UnHighlightTarget();
            await HideTutorialText(cancellationToken);
            
            // NOTE:次チュートリアルがステージ詳細のため、ここでホームに戻っておく
            ShowIndicator("home_footer_main_button");
            await WaitClickEvent(cancellationToken, "home_footer_main_button");
            HideIndicator();

            // ホーム画面待機
            await WaitViewPresentation<HomeMainViewController>(cancellationToken);
        }
        
        async UniTask StageDescriptionTutorial(CancellationToken cancellationToken)
        {
            // メインクエストTOPへ遷移
            ShowIndicator("home_quest_top_button");
            await WaitClickEvent(cancellationToken, "home_quest_top_button");
            HideIndicator();

            // メインクエストTOP画面
            // クエスト解放演出待つ
            await FadeOutGrayOut(cancellationToken);
            await WaitViewPresentation<MainQuestTopViewController>(cancellationToken);
            var releaseView = FindTargetObject("main_quest_top_view").GetComponent<MainQuestTopView>();
            await UniTask.WaitUntil(() => releaseView.CloseQuestReleaseAnimation, cancellationToken: cancellationToken);

            await FadeInGrayOut(cancellationToken);

            // カルーセルのスクロールを無効にする
            var questView = FindTargetObject("home_main_quest_view").GetComponent<HomeMainQuestView>();
            questView.CarouselView.enabled = false;

            await ShowTutorialText(cancellationToken, "挑戦する前にステージの\n詳細情報を確認してみよう！", 0f);
            await HideTutorialText(cancellationToken);

            ShowIndicator("home_stage_cell", t => t.GetComponent<HomeMainStageSelectCell>().StageNumber == 1);
            await WaitClickEvent(cancellationToken, "home_stage_button");
            HideIndicator();
            // ダイアログ下のグレーアウトを解除し、オーバーレイのグレーアウトにする
            HideGrayOut();
            ShowOverlayGrayOut();

            // カルーセルのスクロールを有効にする
            questView.CarouselView.enabled = true;

            // ダイアログ表示まで待つ
            await WaitViewPresentation<HomeStageInfoViewController>(cancellationToken);

            // カルーセルのセルに表示不具合が起こるので表示しなおす
            var cellObject = FindTargetObject(
                "home_stage_cell",
                t => t.GetComponent<HomeMainStageSelectCell>().StageNumber == 1).gameObject;
            cellObject.SetActive(false);
            // カルーセルビューの処理で再表示されるが安全のため1F待って再表示
            await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
            cellObject.SetActive(true);

            // ステージ情報のハイライト
            var infoHeight = FindTargetObject("stage_detail_info").GetRectTransform().rect.height;
            var backAreaRect = FindTargetObject("stage_detail_info_back").GetRectTransform();
            backAreaRect.SetSizeWithCurrentAnchors(
                RectTransform.Axis.Vertical,
                infoHeight
            );

            HighlightTarget("stage_detail_info_back");
            HighlightTarget("stage_detail_info");

            await ShowTutorialText(cancellationToken, "ステージの詳細情報では\n出現ファントムの情報が分かるよ！", -200f);
            await ShowTutorialText(cancellationToken, "クリアが難しくなってきたら\n情報を見て有利なキャラを編成してね！", -200f);
            await HideTutorialText(cancellationToken);
            UnHighlightTarget();
            backAreaRect.SetSizeWithCurrentAnchors(
                RectTransform.Axis.Vertical,
                0f
            );

            ShowIndicator("close_stage_detail_button");
            await WaitDismissModal<HomeStageInfoViewController>(cancellationToken);
            HideIndicator();


            // チュートリアル進捗更新
            await ProgressTutorialStatus(cancellationToken);
        }
        
        
        async UniTask AddParty(CancellationToken cancellationToken)
        {
            // チュートリアルで編成する未編成キャラを取得 (レア度順最大 5体編成)
            var userUnitIds = TutorialPartyFormationUseCase.GetUnitsToAddParty();

            for (var i = 0; i < userUnitIds.Count ; i++)
            {
                var userUnitId = userUnitIds[i];
                if (userUnitId.IsEmpty()) return;

                ShowIndicator(
                    "party_formation_unit_icon",
                    t => t.GetComponent<PartyFormationUnitListCell>().UserUnitId == userUnitId);
                var target = FindTargetObject(
                    "party_formation_unit_icon",
                    t => t.GetComponent<PartyFormationUnitListCell>().UserUnitId == userUnitId);

                if (target == null) continue;

                target.GetComponent<PartyFormationUnitListCell>().LongPress.enabled = false;
                await WaitClickEvent(
                    cancellationToken,
                    "party_formation_unit_icon",
                    t => t.GetComponent<PartyFormationUnitListCell>().UserUnitId == userUnitId);
                HideIndicator();

                await UniTask.Delay(150, cancellationToken: cancellationToken);
            }
        }
        
        async UniTask ApplyPartyFormation(CancellationToken cancellationToken)
        {
            var needsApplyParty = TutorialApplyPartyFormationUseCase.GetNeedApplyPartyFormation();
            await ApplyPartyFormationUseCase.ApplyPartyFormation(cancellationToken, needsApplyParty);
        }

        bool IsPartyFormationApplied()
        {
            var formedPartyUnitList = GameRepository.GetGameFetchOther().UserPartyModels[0].GetUnitList();
            return formedPartyUnitList.Count(unit => !unit.IsEmpty()) > 1;
        }

        async UniTask UpdateGachaConfirmedApplyIfNeeds(CancellationToken cancellationToken)
        {
            ScreenInteractionControl.ActivityBegin();
            await TutorialGachaConfirmedApplyUseCase.UpdateGachaConfirmedApplyIfNeeds(cancellationToken);
            ScreenInteractionControl.ActivityEnd();

            HomeHeaderDelegate.UpdateStatus();
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

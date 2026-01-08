using System.Threading;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.GachaResult.Presentation.Views;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.UnitTab.Presentation.Views;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    public class MainPart1TutorialSequence : BaseTutorialSequence
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] TutorialPartyFormationUseCase TutorialPartyFormationUseCase { get; }
        [Inject] ApplyPartyFormationUseCase ApplyPartyFormationUseCase { get; }
        [Inject] GachaConfirmedApplyUseCase GachaConfirmedApplyUseCase { get; }
        [Inject] TutorialApplyPartyFormationUseCase TutorialApplyPartyFormationUseCase { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        public override async UniTask Play(CancellationToken cancellationToken)
        {
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            if (tutorialStatus.IsStartMainPart1())
            {
                await GachaDrawTutorial(cancellationToken);

                // 更新されているので再取得
                tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;
            }

            if (tutorialStatus.IsGachaConfirmed())
            {
                await PartyFormationTutorial(cancellationToken);
            }

            ShowIndicator("home_main_start_button");
            await WaitClickEvent(cancellationToken, "home_main_start_button");
            HideIndicator();

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
            // チュートリアルガシャバナー全体をハイライト
            HighlightTarget("tutorial_gacha_banner");
            
            // チュートリアルガシャの「ひく」ボタンを強調アニメーション

            var button = FindTargetObject("tutorial_gacha_banner")?.GetComponent<TutorialGachaBannerComponent>();
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

        async UniTask PartyFormationTutorial(CancellationToken cancellationToken)
        {
            // ガシャ結果の確定後の反映が成功しているか確認する(編成・アバター)
            await UpdateGachaConfirmedApplyIfNeeds(cancellationToken);
            
            // エラーで編成は完了し、チュートリアルステータスの更新のみ失敗している場合はステータス更新だけする
            if (IsPartyFormationApplied())
            {
                await ProgressTutorialStatus(cancellationToken);
                return;
            }
            
            await ShowTutorialText(cancellationToken, "仲間になった「キャラ」を\nパーティに入れよう！", 0f);
            await HideTutorialText(cancellationToken);

            // キャラ一覧へ誘導
            ShowIndicator("home_footer_unit_button");
            await WaitClickEvent(cancellationToken, "home_footer_unit_button");
            HideIndicator();

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
            await ShowTutorialText(cancellationToken, "次のバトルに挑戦しよう！", 0f);
            UnHighlightTarget();
            await HideTutorialText(cancellationToken);
            ShowIndicator("home_footer_main_button");
            await WaitClickEvent(cancellationToken, "home_footer_main_button");
            HideIndicator();

            // ホーム画面待機
            await WaitViewPresentation<HomeMainViewController>(cancellationToken);
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
            await GachaConfirmedApplyUseCase.UpdateGachaConfirmedApplyIfNeeds(cancellationToken);
            ScreenInteractionControl.ActivityEnd();

            HomeHeaderDelegate.UpdateStatus();
        }
    }
}

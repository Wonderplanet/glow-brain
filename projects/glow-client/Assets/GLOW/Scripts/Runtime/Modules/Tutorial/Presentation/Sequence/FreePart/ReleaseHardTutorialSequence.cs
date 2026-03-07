using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.MainQuestTop.Presentation;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestSelect.Presentation;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component;
using GLOW.Scenes.QuestSelectList.Presentation;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleaseHardTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            // [ホーム画面]
            await ShowTutorialText(cancellationToken, "クエストの選び方を教えるね！", 0f);
            await HideTutorialText(cancellationToken);

            ShowIndicator("home_quest_top_button");
            await WaitClickEvent(cancellationToken, "home_quest_top_button");
            HideIndicator();

            // [メインクエストTOP画面]
            await WaitViewPresentation<MainQuestTopViewController>(cancellationToken);
            // キャンペーン吹き出しを非表示にする
            var questSelectView = FindTargetObject("main_quest_top_view").GetComponent<MainQuestTopView>();
            questSelectView.SetActiveCampaignBalloon(false);

            ShowIndicator("quest_select_button");
            await WaitClickEvent(cancellationToken, "quest_select_button");
            HideIndicator();
            await WaitViewPresentation<QuestSelectListViewController>(cancellationToken);

            // [クエスト選択画面]
            await ShowTutorialText(cancellationToken, "挑戦するクエストはこの画面で選べるよ", 0f);
            await ShowTutorialText(cancellationToken, "アイコンをタップすると\nクエストを選べるんだ", 0f);
            await HideTutorialText(cancellationToken);

            bool CellSearch(TutorialIndicatorTarget target) =>
                target.GetComponent<QuestSelectListCell>().IsListFirstCell;

            ShowIndicator("quest_icon_button", CellSearch);
            await WaitClickEvent(cancellationToken, "quest_icon_button", CellSearch);
            HideIndicator();

            await WaitViewPresentation<MainQuestTopViewController>(cancellationToken);

            // [メインクエストTOP画面]
            // ハードボタン無効化・ハイライトして演出再生
            var hardButtonComponent = FindTargetObject("hard_button").GetComponent<QuestDifficultyButtonComponent>();
            hardButtonComponent.SetButtonEnabled(false);
            HighlightTarget("hard_button");
            hardButtonComponent.PlayReleaseAnimation();

            await UniTask.Delay(2000, cancellationToken: cancellationToken);
            UnHighlightTarget();

            await ShowTutorialText(cancellationToken, "クエストをクリアすると\n新しい難易度が開放されることもあるよ！", 0f);
            await HideTutorialText(cancellationToken);

            // ハードボタンを有効化する
            hardButtonComponent.SetButtonEnabled(true);

            await ShowTutorialText(cancellationToken, "開放されたクエストアイコンをタップして\n挑戦するクエストを決めよう！", 0f);
            await HideTutorialText(cancellationToken);

            ShowIndicator("quest_select_button");
            await WaitClickEvent(cancellationToken, "quest_select_button");
            HideIndicator();
            await WaitViewPresentation<QuestSelectListViewController>(cancellationToken);

            // [クエスト選択画面]
            await UniTask.Delay(300, cancellationToken: cancellationToken);
            // チュートリアル進捗更新(通信が競合する場合があるため、ここで更新)
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleaseHardStage);

            // newのクエストアイコンを取得
            bool QuestCellSearch(TutorialIndicatorTarget target) => target.GetComponent<QuestSelectListCell>().IsListSecondCell;

            ShowIndicator("quest_icon_button", QuestCellSearch);
            await WaitViewPresentation<MainQuestTopViewController>(cancellationToken);

            // [クエストTOP画面]
            HideIndicator();
            await UniTask.Delay(100, cancellationToken: cancellationToken);

            // 終了
            await FadeOutGrayOut(cancellationToken);
        }
    }
}

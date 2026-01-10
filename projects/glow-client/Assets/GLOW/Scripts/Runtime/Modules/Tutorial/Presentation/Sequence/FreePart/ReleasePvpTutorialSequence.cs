using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.PvpTop.Presentation;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Presentation;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleasePvpTutorialSequence : BaseTutorialSequence
    {
        
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);
            
            // ホーム画面
            await ShowTutorialText(cancellationToken, "ランクマッチが開催中だよ！", 0f);
            await HideTutorialText(cancellationToken);
            
            ShowIndicator("home_footer_content_button");
            await WaitClickEvent(cancellationToken, "home_footer_content_button");
            HideIndicator();
            await WaitViewPresentation<QuestContentTopViewController>(cancellationToken);

            // コンテンツ画面(イベント画面)を取得する
            var questContentTopView = FindTargetObject("QuestContentTopView").GetComponent<QuestContentTopView>();

            // ランクマッチが映るようにスクロールする
            questContentTopView.ScrollToContentCell(QuestContentTopElementType.Pvp);
            
            // スクロールの無効化
            questContentTopView.SetEnableScroll(false);
            
            // まだスクロールしていないので1F待つ(グレーアウトしているのでタップは塞がれている)
            await UniTask.Delay(1, cancellationToken: cancellationToken);
            
            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleasePvp);
            
            // イベント画面のランクマッチセルを取得
            bool PvpCellSearch(TutorialIndicatorTarget target) => target.GetComponent<QuestContentCell>().QuestContentTopElementType == QuestContentTopElementType.Pvp;
            
            ShowIndicator("content_cell_button", PvpCellSearch);
            await WaitClickEvent(cancellationToken, "content_cell_button", PvpCellSearch);
            // スクロール無効化の解除
            questContentTopView.SetEnableScroll(true);
            HideIndicator();

            await WaitViewPresentation<PvpTopViewController>(cancellationToken);

            // 初遷移時のダイアログ表示はTransitPvpTutorialで行う
            

            // 終了
            await FadeOutGrayOut(cancellationToken);
        }
    }
}
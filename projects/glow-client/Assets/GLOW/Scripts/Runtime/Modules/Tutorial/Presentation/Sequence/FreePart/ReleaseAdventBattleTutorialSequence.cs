using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.AdventBattle.Presentation.View;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Presentation;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleaseAdventBattleTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        { 
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            // ホーム画面
            await ShowTutorialText(cancellationToken, "降臨バトルが開催中だよ！", 0);
            await HideTutorialText(cancellationToken);
            
            ShowIndicator("home_footer_content_button");
            await WaitClickEvent(cancellationToken, "home_footer_content_button");
            HideIndicator();
            await WaitViewPresentation<QuestContentTopViewController>(cancellationToken);

            // コンテンツ画面(イベント画面)を取得する
            var questContentTopView = FindTargetObject("QuestContentTopView").GetComponent<QuestContentTopView>();

            // 降臨バトルが映るようにスクロールする
            questContentTopView.ScrollToContentCell(QuestContentTopElementType.AdventBattle);
            
            // スクロールの無効化
            questContentTopView.SetEnableScroll(false);
            
            // まだスクロールしていないので1F待つ(グレーアウトしているのでタップは塞がれている)
            await UniTask.Delay(1, cancellationToken: cancellationToken);
            
            // チュートリアル進捗更新(通信が競合する場合があるため、ここで更新)
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleaseAdventBattle);
            
            // イベント画面のアドベントバトルセルを取得
            bool AdventCellSearch(TutorialIndicatorTarget target) => target.GetComponent<QuestContentCell>().QuestContentTopElementType == QuestContentTopElementType.AdventBattle;
            
            ShowIndicator("content_cell_button", AdventCellSearch);
            await WaitClickEvent(cancellationToken, "content_cell_button", AdventCellSearch);
            HideIndicator();
            
            // スクロール無効化の解除
            questContentTopView.SetEnableScroll(true);

            await WaitViewPresentation<AdventBattleTopViewController>(cancellationToken);

            // 初遷移時のダイアログ表示はAdventBattleTopPresenterで行う

            // 終了
            await FadeOutGrayOut(cancellationToken);
        }
    }
}

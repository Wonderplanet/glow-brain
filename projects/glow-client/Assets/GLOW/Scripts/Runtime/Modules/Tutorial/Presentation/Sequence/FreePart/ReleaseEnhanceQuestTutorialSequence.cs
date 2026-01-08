using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.QuestContentTop.Presentation;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleaseEnhanceQuestTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            var tipId = TutorialFreePartIdDefinitions.ReleaseEnhanceQuest.ToMasterDataId();
            var tipModels = GetTutorialTips(tipId);
            
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);
         
            await ShowTutorialText(cancellationToken, "キャラ強化に「コイン」は足りてるかな？", 0f);
            await ShowTutorialText(cancellationToken, "今回は「コイン」を\nたくさん集められるクエストを教えるね！", 0f);
            await HideTutorialText(cancellationToken);

            // ホーム画面
            ShowIndicator("home_footer_content_button");
            await WaitClickEvent(cancellationToken, "home_footer_content_button");
            HideIndicator();
            await WaitViewPresentation<QuestContentTopViewController>(cancellationToken);
            
            // コンテンツ画面(強化クエスト)
            // コンテンツチュートリアルダイアログ
            ShowTutorialDialog(tipModels[0].Title, tipModels[0].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
            
            await ShowTutorialText(cancellationToken, "「コイン獲得クエスト」は\nスタミナを使わずに挑戦できるよ！", 0f);
            await ShowTutorialText(cancellationToken, "1日に挑戦できる回数が決まってるから\n毎日忘れずに挑戦しよう！", 0f);
            await HideTutorialText(cancellationToken);
            
            // 終了
            await FadeOutGrayOut(cancellationToken);
            
            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleaseEnhanceQuest);
        }
    }
}
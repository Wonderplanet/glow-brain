using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;
using GLOW.Modules.TutorialTipDialog.Presentation.View;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class TransitPvpTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // ランクマッチ画面
            
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);
            
            // ダイアログ表示
            var adventBattleTipId = TutorialFreePartIdDefinitions.TransitPvp.ToMasterDataId();
            var tipModels = GetTutorialTips(adventBattleTipId);
            
            ShowTutorialDialogWithNextButton(tipModels[0].Title, tipModels[0].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
            
            ShowTutorialDialogWithNextButton(tipModels[1].Title, tipModels[1].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
            
            ShowTutorialDialog(tipModels[2].Title, tipModels[2].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
            
            await ShowTutorialText(cancellationToken, "ランクマッチは、他のリーダーの\nパーティとバトルするよ！！", 0f);
            await ShowTutorialText(cancellationToken, "ランクマッチでは、\n相手も「JUMBLE RUSH」を\n使ってくるんだ！", 0f);
            await ShowTutorialText(cancellationToken, "いつもとは違うバトルになるから\nランクマッチ用のパーティを\n組んで参加しよう！", 0f);
            await HideTutorialText(cancellationToken);

            HighlightTarget("pvp_top_opponents");
            
            await ShowTutorialText(cancellationToken, "対戦相手は3人の中から選ぼう！", -450f);
            await HideTutorialText(cancellationToken);
            
            UnHighlightTarget();
            
            ShowArrowIndicator("pvp_top_opponent_info", ReverseFlag.False);
            
            await ShowTutorialText(cancellationToken, "対戦相手のパーティを見たい時は\nインフォメーションボタンを押してね", -450f);
            await HideTutorialText(cancellationToken);
            
            HideArrowIndicator();
            
            ShowArrowIndicator("pvp_top_opponent_reset", ReverseFlag.False);
            
            await ShowTutorialText(cancellationToken, "更新ボタンを押せば\n対戦相手を変更できるよ！", -450f);
            await HideTutorialText(cancellationToken);
            
            HideArrowIndicator();
            
            HighlightTarget("pvp_top_stage_detail");
            
            await ShowTutorialText(cancellationToken, "シーズンによって\nステージが変わることもあるよ！", -450f);
            await ShowTutorialText(cancellationToken, "バトルの前には\nステージ詳細を確認しておこう！", -450f);
            await HideTutorialText(cancellationToken);
            
            UnHighlightTarget();
            
            HighlightTarget("pvp_top_ranking");
            
            await ShowTutorialText(cancellationToken, "ランクマッチではランキングも開催！", -450f);
            await ShowTutorialText(cancellationToken, "勝利ポイントを増やして\nランキング上位を目指そう！", -450f);
            await HideTutorialText(cancellationToken);
            
            UnHighlightTarget();
            
            
            // 終了
            await FadeOutGrayOut(cancellationToken);

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.TransitPvp);
        }
    }
}
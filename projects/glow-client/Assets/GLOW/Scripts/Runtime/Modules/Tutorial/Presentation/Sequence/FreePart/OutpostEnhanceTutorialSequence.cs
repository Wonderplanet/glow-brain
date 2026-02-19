using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Presentation.View;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class OutpostEnhanceTutorialSequence : BaseTutorialSequence
    {

        public override async UniTask Play(CancellationToken cancellationToken)
        {
            var adventBattleTipId = TutorialFreePartIdDefinitions.OutpostEnhance.ToMasterDataId();
            var tipModels = GetTutorialTips(adventBattleTipId);

            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            // ホーム画面
            await ShowTutorialText(cancellationToken, "「ゲート強化」をすると\nパーティ全体を強くできるんだ！", 0);
            await HideTutorialText(cancellationToken);

            // ダイアログ表示
            ShowTutorialDialog(tipModels[0].Title, tipModels[0].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

            await ShowTutorialText(cancellationToken, "コインがたまったら\nゲート強化をしてみよう！", 0);
            await HideTutorialText(cancellationToken);

            // 終了
            await FadeOutGrayOut(cancellationToken);

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.OutpostEnhance);
        }
    }
}
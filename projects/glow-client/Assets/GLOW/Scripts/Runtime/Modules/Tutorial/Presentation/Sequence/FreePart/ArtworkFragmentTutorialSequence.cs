using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Presentation.View;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ArtworkFragmentTutorialSequence : BaseInGameTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            var tipId = TutorialFreePartIdDefinitions.ArtworkFragment.ToMasterDataId();
            var tipModels = GetTutorialTips(tipId);
            
            // 原画のかけらチュートリアルダイアログ1
            ShowTutorialDialogWithNextButton(tipModels[0].Title, tipModels[0].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

            // 原画のかけらチュートリアルダイアログ2
            ShowTutorialDialogWithNextButton(tipModels[1].Title, tipModels[1].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
            
            // 原画のかけらチュートリアルダイアログ3
            ShowTutorialDialog(tipModels[2].Title, tipModels[2].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

            // 終了
            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ArtworkFragment);
        }
    }
}
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;
using GLOW.Modules.TutorialTipDialog.Presentation.View;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class TransitArtworkEffectTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            // 編成画面
            await ShowTutorialText(cancellationToken, "原画編成を確認してみよう", 0f);
            await HideTutorialText(cancellationToken);

            // 編成画面・原画効果タブ
            ShowIndicator("artwork_effect_tab_button");
            await WaitClickEvent(cancellationToken, "artwork_effect_tab_button");
            HideIndicator();

            // 画面切り替え待ち
            await UniTask.Delay(300, cancellationToken: cancellationToken);

            // ダイアログ表示
            var tutorialMstId = TutorialFreePartIdDefinitions.TransitArtworkEffect.ToMasterDataId();
            var tipModels = GetTutorialTips(tutorialMstId);

            ShowTutorialDialogWithNextButton(tipModels[0].Title, tipModels[0].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

            ShowTutorialDialogWithNextButton(tipModels[1].Title, tipModels[1].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

            ShowTutorialDialog(tipModels[2].Title, tipModels[2].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

            // 終了
            await FadeOutGrayOut(cancellationToken);

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.TransitArtworkEffect);
        }
    }
}

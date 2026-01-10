using System;
using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;
using UIKit;

namespace GLOW.Modules.TutorialTipDialog.Presentation.View
{
    public class TutorialTipDialogViewController :
        UIViewController<TutorialTipDialogView>
    {
        Action _onTipDialogClosedAction;

        public static TutorialTipDialogViewController WithTitleAndAssetPath(
            TutorialTipDialogTitle title,
            TutorialTipAssetPath assetPath,
            ShouldShowNextButtonTextFlag shouldShowNextButtonText,
            Action callback = null)
        {
            var controller = new TutorialTipDialogViewController();
            controller.SetupTitle(title);
            controller.SetupTipImage(assetPath);
            controller._onTipDialogClosedAction = callback;

            if (shouldShowNextButtonText)
            {
                controller.ShowNextButtonText();
            }

            return controller;
        }

        public void CloseButtonTapped()
        {
            _onTipDialogClosedAction?.Invoke();
            Dismiss();
        }

        void SetupTitle(TutorialTipDialogTitle title)
        {
            ActualView.SetupTitle(title);
        }

        void SetupTipImage(TutorialTipAssetPath assetPath)
        {
            ActualView.SetupTipImage(assetPath);
        }

        void ShowNextButtonText()
        {
            ActualView.ShowNextButtonText();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            CloseButtonTapped();
        }
    }
}

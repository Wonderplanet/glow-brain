using GLOW.Core.Presentation.Components;
using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Modules.TutorialTipDialog.Presentation.View
{
    public class TutorialTipDialogView : UIView
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UIImage _tipImage;
        [SerializeField] UIText _okButtonText;
        [SerializeField] UIText _nextButtonText;
        
        
        public void SetupTitle(TutorialTipDialogTitle title)
        {
            _titleText.SetText(title.Value);
        }

        public void SetupTipImage(TutorialTipAssetPath assetPath)
        {
            UISpriteUtil.LoadSpriteWithFade(_tipImage.Image, assetPath.Value);
        }
        
        public void ShowNextButtonText()
        {
            _nextButtonText.IsVisible = true;
            _okButtonText.IsVisible = false;
        }

    }
}

using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Inquiry.Presentation.View
{
    public class InquiryDialogView : UIView
    {
        [SerializeField] UIText _idText;

        public void Initialize(InquiryDialogViewModel viewModel)
        {
            if (viewModel.MyId.IsEmpty())
            {
                Debug.LogError($"UserIdが空になっています");
                return;
            }

            _idText.SetText(viewModel.MyId.Value);
        }
    }
}

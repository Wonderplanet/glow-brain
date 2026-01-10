using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PvpInfo.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpInfo.Presentation.View
{
    public class PvpInfoView : UIView
    {
        [SerializeField] UIText _descriptionText;

        public void Setup(PvpInfoViewModel viewModel)
        {
            _descriptionText.SetText(viewModel.Description.Value);
        }
    }
}

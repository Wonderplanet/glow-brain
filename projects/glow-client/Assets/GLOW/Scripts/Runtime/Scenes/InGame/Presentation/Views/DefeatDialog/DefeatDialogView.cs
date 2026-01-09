using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Views.DefeatDialog
{
    public class DefeatDialogView : UIView
    {
        [SerializeField] UIText _descriptionText;

        public void SetDescription(DefeatDescription defeatDescription)
        {
            _descriptionText.SetText(defeatDescription.Value);
        }
    }
}

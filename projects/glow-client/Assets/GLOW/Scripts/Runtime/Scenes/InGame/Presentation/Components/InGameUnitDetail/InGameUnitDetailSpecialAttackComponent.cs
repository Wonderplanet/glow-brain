using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail
{
    public class InGameUnitDetailSpecialAttackComponent : UIObject
    {
        [SerializeField] UIText _specialAttackName;
        [SerializeField] UIText _descriptionText;
        [SerializeField] UIText _coolTimeText;

        public void Setup(InGameUnitDetailSpecialAttackViewModel viewModel)
        {
            _specialAttackName.SetText(viewModel.Name.Value);
            _descriptionText.SetText(viewModel.Description.Value);
            _coolTimeText.SetText(viewModel.CoolTime.ToCoolTimeString());
        }
    }
}

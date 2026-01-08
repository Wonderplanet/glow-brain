using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EventBonusUnitList.Presentation.Views
{
    public class EventBonusUnitListCell : UICollectionViewCell
    {
        [SerializeField] CharacterIconComponent _icon;
        [SerializeField] CharacterBonusIconComponent _bonusIcon;

        public void Setup(CharacterIconViewModel icon, EventBonusPercentage bonus)
        {
            _icon.Setup(icon);
            _bonusIcon.Hidden = bonus.IsEmpty();
            _bonusIcon.Setup(bonus);
        }
    }
}

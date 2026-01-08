using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Components;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationUnitListCell : UICollectionViewCell
    {
        [SerializeField] CharacterLongIconComponent _characterIconComponent;
        [SerializeField] CharacterBonusIconComponent _bonusIcon;
        [SerializeField] UIButtonLongPress _longPress;
        [SerializeField] UIObject _badge;
        [SerializeField] UIObject _specialRuleBadge;
        [SerializeField] UIObject _grayOutMask;
        [SerializeField] UIObject _pvpSpecialRuleIcon;

        // チュートリアルで使用
        public UserDataId UserUnitId {get; private set; }
        public bool IsSelectable => _grayOutMask.Hidden;

        public UIButtonLongPress LongPress => _longPress;

        public void Setup(PartyFormationUnitListCellViewModel viewModel)
        {
            UserUnitId = viewModel.UserUnitId;
            _characterIconComponent.Setup(viewModel.CharacterIconViewModel, viewModel.IsAssigned.Value, viewModel.SortType);
            _badge.Hidden = !viewModel.NotificationBadge;

            _bonusIcon.Hidden = viewModel.EventBonusPercentage.IsEmpty();
            _bonusIcon.Setup(viewModel.EventBonusPercentage);
            _specialRuleBadge.Hidden = viewModel.IsAchievedSpecialRule;
            var selectable = viewModel.IsSelectable && viewModel.IsAchievedSpecialRule;
            _grayOutMask.Hidden = selectable;
            _pvpSpecialRuleIcon.Hidden = !viewModel.IsInGameSpecialRuleUnitStatusTarget;
        }
    }
}

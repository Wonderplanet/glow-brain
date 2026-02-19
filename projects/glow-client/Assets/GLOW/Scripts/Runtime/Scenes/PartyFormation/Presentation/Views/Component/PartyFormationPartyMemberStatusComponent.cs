using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationPartyMemberStatusComponent : UIObject
    {
        [SerializeField] UIText _cost;
        [SerializeField] UIText _levelText;
        [SerializeField] UIText _statusStringText;
        [SerializeField] UIText _statusValueText;
        [SerializeField] IconRarityImage _iconRarityImage;
        [SerializeField] CharacterStatusIconComponent _characterStatusIcon;
        [SerializeField] IconCharaGrade _iconCharaGrade;
        [SerializeField] GameObject _uiRoot;
        [SerializeField] UIObject _costRoot;
        [SerializeField] UIObject _levelRoot;
        [SerializeField] UIObject _statusIconRoot;
        [SerializeField] UIObject _pvpSpecialRuleIcon;
        [SerializeField] CharacterBonusIconComponent _bonusIcon;
        [SerializeField] Canvas _statusCanvas;

        public void Setup(PartyFormationPartyMemberViewModel viewModel)
        {
            _uiRoot.SetActive(true);
            _cost.SetText(viewModel.Cost.ToString());
            _levelText.SetText(viewModel.Level.ToString());
            _iconRarityImage.Setup(viewModel.Rarity);
            _characterStatusIcon.Setup(viewModel.SortType);
            _iconCharaGrade.SetGrade(viewModel.Grade);
            _bonusIcon.Setup(viewModel.EventBonus);
            _bonusIcon.Hidden = viewModel.EventBonus.IsEmpty();
            _pvpSpecialRuleIcon.IsVisible = viewModel.IsInGameSpecialRuleUnitStatusTarget;
            SetStatusCanvasEnabled(true);

            SetStatusHidden();
            switch (viewModel.SortType)
            {
                case UnitListSortType.Hp:
                    _statusIconRoot.Hidden = false;
                    _statusValueText.Hidden = false;
                    _statusValueText.SetText(viewModel.GetHpText());
                    break;
                case UnitListSortType.Attack:
                    _statusIconRoot.Hidden = false;
                    _statusValueText.Hidden = false;
                    _statusValueText.SetText(viewModel.GetAttackPowerText());
                    break;
                case UnitListSortType.AttackRange:
                    _statusIconRoot.Hidden = false;
                    _statusStringText.Hidden = false;
                    _statusStringText.SetText(viewModel.GetAttackRangeText());
                    break;
                case UnitListSortType.Speed:
                    _statusIconRoot.Hidden = false;
                    _statusStringText.Hidden = false;
                    _statusStringText.SetText(viewModel.GetMoveSpeedText());
                    break;
                case UnitListSortType.Rarity:
                    _iconRarityImage.Hidden = false;
                    break;
                case UnitListSortType.Grade:
                    _iconCharaGrade.Hidden = false;
                    break;
                case UnitListSortType.Level:
                    _levelRoot.Hidden = false;
                    _levelText.Hidden = false;
                    break;
                default:
                    _costRoot.Hidden = false;
                    _statusValueText.Hidden = false;
                    _statusValueText.SetText(viewModel.Cost.ToString());
                    break;
            }
        }

        public void SetStatusCanvasEnabled(bool isEnabled)
        {
            _statusCanvas.overrideSorting = isEnabled;
        }

        void SetStatusHidden()
        {
            _costRoot.Hidden = true;
            _levelRoot.Hidden = true;
            _statusIconRoot.Hidden = true;
            _levelText.Hidden = true;
            _statusValueText.Hidden = true;
            _statusStringText.Hidden = true;
            _iconRarityImage.Hidden = true;
            _iconCharaGrade.Hidden = true;
        }
    }
}

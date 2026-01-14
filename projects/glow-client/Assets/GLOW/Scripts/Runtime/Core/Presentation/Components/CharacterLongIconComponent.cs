using GLOW.Core.Domain.Extensions;
using GLOW.Core.Presentation.Constants;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Domain.Constants;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Components
{
    public class CharacterLongIconComponent : UIObject, ICharacterIconComponent
    {
        [SerializeField] IconRarityFrame _rarityFrame;
        [SerializeField] CharaRoleIcon _roleIcon;
        [SerializeField] CharacterColorIcon _colorIcon;
        [SerializeField] UIImage _characterIcon;
        [SerializeField] GameObject _assignedStatusObj;
        [SerializeField] UIText _levelText;
        [SerializeField] UIImage _summonCostIcon;
        [SerializeField] UIText _statusValueText;
        [SerializeField] UIText _statusStringText;
        [SerializeField] IconCharaGrade _grade;
        [SerializeField] IconRarityImage _rarityIcon;
        [SerializeField] CharacterStatusIconComponent _statusIcon;

        public void Setup(
            CharacterIconViewModel viewModel,
            bool isAssigned = false,
            UnitListSortType sortType = UnitListSortType.Rarity)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _characterIcon.Image,
                viewModel.IconAssetPath.Value);

            _roleIcon.SetupCharaRoleIcon(viewModel.Role);
            _rarityFrame.Setup(IconRarityFrameType.Unit, viewModel.Rarity);
            _levelText.SetText(viewModel.Level.ToString());
            _assignedStatusObj.SetActive(isAssigned);
            _grade.SetGrade(viewModel.Grade);
            _rarityIcon.Setup(viewModel.Rarity);
            _statusIcon.Setup(sortType);
            _colorIcon.SetupCharaColorIcon(viewModel.Color);

            switch (sortType)
            {
                case UnitListSortType.Hp:
                    _summonCostIcon.Hidden = true;
                    _statusValueText.Hidden = false;
                    _statusStringText.Hidden = true;
                    _roleIcon.Hidden = true;
                    _colorIcon.Hidden = true;

                    _statusValueText.SetText(viewModel.GetHpText());
                    break;
                case UnitListSortType.Attack:
                    _summonCostIcon.Hidden = true;
                    _statusValueText.Hidden = false;
                    _statusStringText.Hidden = true;
                    _roleIcon.Hidden = true;
                    _colorIcon.Hidden = true;

                    _statusValueText.SetText(viewModel.GetAttackPowerText());
                    break;
                case UnitListSortType.AttackRange:
                    _summonCostIcon.Hidden = true;
                    _statusValueText.Hidden = true;
                    _statusStringText.Hidden = false;
                    _roleIcon.Hidden = true;
                    _colorIcon.Hidden = true;

                    _statusStringText.SetText(viewModel.GetAttackRangeText());
                    break;
                case UnitListSortType.Speed:
                    _summonCostIcon.Hidden = true;
                    _statusValueText.Hidden = true;
                    _statusStringText.Hidden = false;
                    _roleIcon.Hidden = true;
                    _colorIcon.Hidden = true;

                    _statusStringText.SetText(viewModel.GetMoveSpeedText());
                    break;
                default:
                    _summonCostIcon.Hidden = false;
                    _statusValueText.Hidden = false;
                    _statusStringText.Hidden = true;
                    _roleIcon.Hidden = false;
                    _colorIcon.Hidden = false;

                    _statusValueText.SetText(viewModel.SummonCost.ToString());
                    break;
            }
        }
    }
}

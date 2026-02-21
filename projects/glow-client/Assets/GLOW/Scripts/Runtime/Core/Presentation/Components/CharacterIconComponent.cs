using GLOW.Core.Presentation.Constants;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Domain.Constants;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Components
{
    public class CharacterIconComponent : UIObject, ICharacterIconComponent
    {
        [SerializeField] IconRarityFrame _rarityFrame;
        [SerializeField] CharaRoleIcon _roleIcon;
        [SerializeField] CharacterColorIcon _characterColorIcon;
        [SerializeField] UIImage _characterIcon;
        [SerializeField] GameObject _assignedStatusObj;
        [SerializeField] UIText _levelText;
        [SerializeField] UIText _summonCost;
        [SerializeField] IconCharaGrade _grade;

        public void Setup(
            CharacterIconViewModel viewModel,
            bool isAssigned = false,
            UnitListSortType sortType = UnitListSortType.Rarity)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _characterIcon.Image,
                viewModel.IconAssetPath.Value);

            _roleIcon.SetupCharaRoleIcon(viewModel.Role);
            _characterColorIcon.SetupCharaColorIcon(viewModel.Color);
            _rarityFrame.Setup(IconRarityFrameType.Unit, viewModel.Rarity);
            _levelText.SetText(viewModel.Level.ToString());
            _summonCost.SetText(viewModel.SummonCost.ToString());
            _assignedStatusObj.SetActive(isAssigned);
            _grade.SetGrade(viewModel.Grade);
        }

        public void SetupNoStatus(CharacterIconViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _characterIcon.Image,
                viewModel.IconAssetPath.Value);

            _rarityFrame.Setup(IconRarityFrameType.Unit, viewModel.Rarity);
            _roleIcon.Hidden = true;
            _levelText.Hidden = true;
            _summonCost.Hidden = true;
            _assignedStatusObj.SetActive(false);
            _grade.Hidden = true;

        }
    }
}

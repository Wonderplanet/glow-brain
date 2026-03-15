using GLOW.Core.Presentation.Components;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-1_ヒーローキャラ表示
    /// </summary>
    public class EncyclopediaUnitDetailView : UIView
    {
        [SerializeField] UnitAvatarPageListComponent _unitAvatarPageListComponent;
        [SerializeField] SeriesLogoComponent _seriesLogo;

        [Header("ヘッダー")]
        [SerializeField] CharaRoleIcon _roleIcon;
        [SerializeField] UnitRarityIcon _rarityIcon;
        [SerializeField] UIText _unitName;

        [Header("ユニット情報")]
        [SerializeField] UIText _unitDescription;
        [SerializeField] ScrollRect _scrollRect;

        [Header("必殺ワザ情報")]
        [SerializeField] UIText _specialAttackName;
        [SerializeField] Button _specialAttackButton;

        public UnitAvatarPageListComponent UnitAvatarPageListComponent => _unitAvatarPageListComponent;

        public void Setup(EncyclopediaUnitDetailViewModel viewModel)
        {
            _roleIcon.SetupCharaRoleIcon(viewModel.RoleType);
            _rarityIcon.Setup(viewModel.Rarity);
            _unitName.SetText(viewModel.Name.Value);
            _seriesLogo.Setup(viewModel.SeriesLogoImagePath);
            _unitDescription.SetText(viewModel.Description.Value);
            _specialAttackName.SetText(viewModel.SpecialAttackName.Value);
            _scrollRect.normalizedPosition = Vector2.up;
        }

        public void SetSpecialAttackButton(bool interactable)
        {
            _specialAttackButton.interactable = interactable;
        }

    }
}

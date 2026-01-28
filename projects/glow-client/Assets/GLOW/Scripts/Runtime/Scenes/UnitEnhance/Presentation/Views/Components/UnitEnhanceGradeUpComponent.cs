using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceGradeUpComponent : UIObject
    {
        [SerializeField] UnitEnhanceGradeUpRequireItemComponent _requireItemComponent;
        [SerializeField] UIText _gradeUpCost;
        [SerializeField] UIText _gradeUpCostSufficient;
        [SerializeField] UIImage _gradeUpCostIcon;
        [SerializeField] Button _gradeUpButton;
        [SerializeField] GameObject _noGradeUpLabel;
        [SerializeField] GameObject _gradeUpBadge;

        public void Setup(UnitEnhanceGradeUpTabViewModel tabViewModel)
        {
            _requireItemComponent.Setup(
                tabViewModel.RequireItemIconViewModel,
                tabViewModel.RequireItemAmount,
                tabViewModel.PossessionItemAmount,
                tabViewModel.ItemName);
            var isEnableGradeUp = !tabViewModel.RequireItemAmount.IsEmpty();
            _noGradeUpLabel.SetActive(!isEnableGradeUp);
            _gradeUpButton.gameObject.SetActive(isEnableGradeUp);
            _gradeUpBadge.SetActive(tabViewModel.IsGradeUp.Value);
            if (tabViewModel.RequireItemAmount <= tabViewModel.PossessionItemAmount)
            {
                _gradeUpCost.SetText(tabViewModel.RequireItemAmount.ToStringSeparated());
                _gradeUpCost.gameObject.SetActive(true);
                _gradeUpCostSufficient.gameObject.SetActive(false);
            }
            else
            {
                _gradeUpCostSufficient.SetText(tabViewModel.RequireItemAmount.ToStringSeparated());
                _gradeUpCost.gameObject.SetActive(false);
                _gradeUpCostSufficient.gameObject.SetActive(true);
            }
            if (!tabViewModel.RequireItemIconViewModel.ItemIconAssetPath.IsEmpty())
                UISpriteUtil.LoadSpriteWithFade(_gradeUpCostIcon.Image, tabViewModel.RequireItemIconViewModel.ItemIconAssetPath.Value);
        }
    }
}

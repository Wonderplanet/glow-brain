using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views
{
    public class UnitFilterView : UIComponent
    {
        [SerializeField] RarityFilterCell _rarityFilterCell;
        [SerializeField] RoleFilterCell _roleFilterCell;
        [SerializeField] RangeFilterCell _rangeFilterCell;
        [SerializeField] SeriesFilterCell _seriesFilterCell;
        [SerializeField] SpecialFilterCell _specialFilterCell;
        [SerializeField] PropertyFilterCell _propertyFilterCell;
        [SerializeField] ColorFilterCell _colorFilterCell;
        [SerializeField] BonusFilterCell _bonusFilterCell;
        [SerializeField] FormationFilterCell _formationFilterCell;

        public IReadOnlyList<Rarity> RarityOnToggleTypes => _rarityFilterCell.GetOnToggleTypes();
        public IReadOnlyList<CharacterColor> ColorOnToggleTypes => _colorFilterCell.GetOnToggleTypes();
        public IReadOnlyList<CharacterUnitRoleType> RoleOnToggleTypes => _roleFilterCell.GetOnToggleTypes();
        public IReadOnlyList<CharacterAttackRangeType> RangeOnToggleTypes => _rangeFilterCell.GetOnToggleTypes();
        public IReadOnlyList<MasterDataId> SeriesOnToggleMasterDataIds => _seriesFilterCell.GetOnToggleMasterDataIds();
        public IReadOnlyList<FilterSpecialAttack> SpecialAttackOnToggleTypes => _specialFilterCell.GetOnToggleTypes();
        public IReadOnlyList<UnitAbilityType> AbilityOnToggleTypes => _propertyFilterCell.GetOnToggleTypes();
        public FilterBonusFlag BonusOnToggle => _bonusFilterCell.GetToggleOn();
        public FilterAchievedSpecialRuleFlag AchievedOnToggle => _formationFilterCell.GetAchievedToggleOn();
        public FilterNotAchieveSpecialRuleFlag NotAchieveOnToggle => _formationFilterCell.GetNotAchieveToggleOn();

        public void Initialize(UnitSortAndFilterDialogViewModel viewModel)
        {
            _rarityFilterCell.Initialize(viewModel.CategoryModel.FilterRarityModel);
            _roleFilterCell.Initialize(viewModel.CategoryModel.FilterRoleModel);
            _rangeFilterCell.Initialize(viewModel.CategoryModel.FilterAttackRangeModel);
            _seriesFilterCell.Initialize(viewModel.CategoryModel.FilterSeriesModel, viewModel.SeriesFilterTitleModels);
            _specialFilterCell.Initialize(viewModel.CategoryModel.FilterSpecialAttackModel);
            _propertyFilterCell.Initialize(viewModel.CategoryModel.FilterAbilityModel, viewModel.UnitAbilityFilterTitleModels);
            _colorFilterCell.Initialize(viewModel.CategoryModel.FilterColorModel);
            _bonusFilterCell.Initialize(viewModel.CategoryModel.FilterBonusModel);
            _formationFilterCell.Initialize(viewModel.CategoryModel.FilterFormationModel);
        }
    }
}

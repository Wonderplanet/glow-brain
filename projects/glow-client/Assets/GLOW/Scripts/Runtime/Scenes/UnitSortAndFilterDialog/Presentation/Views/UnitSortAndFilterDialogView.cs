using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Constants;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views
{
    public class UnitSortAndFilterDialogView : UIView
    {
        [SerializeField] GameObject _sortRoot;
        [SerializeField] GameObject _filterRoot;

        [SerializeField] UnitSortView _sortView;
        [SerializeField] UnitFilterView _filterView;

        [SerializeField] UIToggleableComponentGroup _tabToggleableComponentGroup;

        public IReadOnlyList<Rarity> RarityOnToggleTypes => _filterView.RarityOnToggleTypes;
        public IReadOnlyList<CharacterColor> ColorOnToggleTypes => _filterView.ColorOnToggleTypes;
        public IReadOnlyList<CharacterUnitRoleType> RoleOnToggleTypes => _filterView.RoleOnToggleTypes;
        public IReadOnlyList<CharacterAttackRangeType> RangeOnToggleTypes => _filterView.RangeOnToggleTypes;
        public IReadOnlyList<MasterDataId> SeriesOnToggleMasterDataIds => _filterView.SeriesOnToggleMasterDataIds;
        public IReadOnlyList<FilterSpecialAttack> SpecialAttackOnToggleTypes => _filterView.SpecialAttackOnToggleTypes;
        public IReadOnlyList<UnitAbilityType> AbilityOnToggleTypes => _filterView.AbilityOnToggleTypes;
        public FilterBonusFlag BonusOnToggle => _filterView.BonusOnToggle;
        public FilterAchievedSpecialRuleFlag AchievedOnToggle => _filterView.AchievedOnToggle;
        public FilterNotAchieveSpecialRuleFlag NotAchieveOnToggle => _filterView.NotAchieveOnToggle;

        public void InitializeSort(UnitListSortType currentSortType, Action<UnitListSortType> onToggleChange)
        {
            _sortView.Initialize(currentSortType, onToggleChange);
        }

        public void InitializeFilter(UnitSortAndFilterDialogViewModel viewModel)
        {
            _filterView.Initialize(viewModel);
        }

        public void SetTab(UnitSortAndFilterTabType tabType, UnitListSortType currentSortType)
        {
            _tabToggleableComponentGroup.SetToggleOn(tabType.ToString());

            var isSort = tabType == UnitSortAndFilterTabType.Sort;
            _sortRoot.SetActive(isSort);
            _filterRoot.SetActive(!isSort);

            if (isSort)
            {
                _sortView.SetToggle(currentSortType);
            }
        }

        public void SetSortToggle(UnitListSortType setSortType)
        {
            _sortView.SetToggle(setSortType);
        }

        public void SetEventBonusSortItemHidden(FilterBonusFlag bonusFlag)
        {
            _sortView.SetEventBonusSortItemHidden(bonusFlag);
        }
    }
}

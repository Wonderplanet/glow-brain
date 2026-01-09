using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhance.Presentation.Views.Components;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Component
{
    public class UnitEnhanceRankUpDetailCell : UIObject
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _requiredLevelText;
        [SerializeField] UIObject _grayOut;
        [SerializeField] UIText _grayOutText;
        [SerializeField] List<UnitEnhanceRequireItemIconComponent> _requireItemIcons;
        [Header("特性")]
        [SerializeField] UnitEnhanceAbilityCellComponent[] _abilityList;
        [Header("基礎ステータス")]
        [SerializeField] UIObject _baseStatus;
        [SerializeField] UIText _hpText;
        [SerializeField] UIText _addHpText;
        [SerializeField] UIText _attackText;
        [SerializeField] UIText _addAttackText;
        [Header("スペシャルキャラステータス")]
        [SerializeField] UIObject _specialStatus;
        [SerializeField] UIText _rushText;
        [SerializeField] UIText _addRushText;

        public void Setup(
            UnitEnhanceRankUpDetailCellViewModel viewModel,
            Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            _titleText.SetText($"Lv.{viewModel.LimitLevel}まで開放");
            _grayOutText.SetText($"Lv.{viewModel.LimitLevel}まで開放");
            _requiredLevelText.SetText(viewModel.RequiredLevel.ToString());

            SetupAbility(viewModel);
            _grayOut.Hidden = !viewModel.IsComplete;
            SetupStatus(viewModel);
            SetupRequireItems(viewModel.RequireItems, onItemTapped);
        }

        public void SetupStatus(UnitEnhanceRankUpDetailCellViewModel viewModel)
        {
            _baseStatus.Hidden = viewModel.RoleType == CharacterUnitRoleType.Special;
            _specialStatus.Hidden = viewModel.RoleType != CharacterUnitRoleType.Special;

            _hpText.SetText(viewModel.Hp.ToString());
            _addHpText.SetText($"+{viewModel.AddHp.ToString()}");
            _attackText.SetText(viewModel.AttackPower.ToStringN0());
            _addAttackText.SetText($"+{viewModel.AddAttackPower.ToStringN0()}");

            _rushText.SetText("{0}%", viewModel.AttackPower.ToRushPercentageM().ToStringF2());
            _addRushText.SetText("+{0}%",viewModel.AddAttackPower.ToRushPercentageM().ToStringF2());
        }

        void SetupRequireItems(
            IReadOnlyList<UnitEnhanceRequireItemViewModel> viewModels,
            Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            for (int i = 0; i < _requireItemIcons.Count && i < viewModels.Count; ++i)
            {
                _requireItemIcons[i].Setup(viewModels[i], onItemTapped);
            }

            for (int i = viewModels.Count; i < _requireItemIcons.Count; ++i)
            {
                _requireItemIcons[i].Hidden = true;
            }
        }

        void SetupAbility(UnitEnhanceRankUpDetailCellViewModel viewModel)
        {
            foreach (var cell in _abilityList)
            {
                cell.gameObject.SetActive(false);
            }

            if (viewModel.AbilityViewModels.Count > 0)
            {
                for (int i = 0; i < viewModel.AbilityViewModels.Count; i++)
                {
                    if (i >= _abilityList.Length) break;

                    _abilityList[i].gameObject.SetActive(true);
                    _abilityList[i].Setup(viewModel.AbilityViewModels[i]);
                }
            }
        }
    }
}

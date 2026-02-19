using System;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceUnitStatusComponent : UIObject
    {
        [Header("基本ステータス")]
        [SerializeField] UIObject _baseStatus;
        [SerializeField] UIText _hp;
        [SerializeField] UIText _attackPower;
        [SerializeField] UIText _attackRange;
        [SerializeField] UIText _moveSpeed;

        [Header("スペシャルキャラステータス")]
        [SerializeField] UIObject _specialStatus;
        [SerializeField] UIText _rush;

        public void Setup(UnitEnhanceUnitStatusViewModel viewModel)
        {
            _baseStatus.Hidden = viewModel.RoleType == CharacterUnitRoleType.Special;
            _specialStatus.Hidden = viewModel.RoleType != CharacterUnitRoleType.Special;

            _hp.SetText(viewModel.Hp.ToString());
            _attackPower.SetText(viewModel.AttackPower.ToStringN0());
            _attackRange.SetText(viewModel.AttackRange.ToLocalizeString());
            _moveSpeed.SetText(viewModel.MoveSpeed.ToConvertedString());

            _rush.SetText("{0}%", viewModel.AttackPower.ToRushPercentageM().ToStringF2());
        }
    }
}

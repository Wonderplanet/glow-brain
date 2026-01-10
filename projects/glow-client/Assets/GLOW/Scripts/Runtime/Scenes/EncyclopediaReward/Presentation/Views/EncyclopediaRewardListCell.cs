using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.Views
{
    public class EncyclopediaRewardListCell : UIObject
    {
        [SerializeField] UIText _rank;
        [SerializeField] PlayerResourceIconButtonComponent _rewardIcon;
        [SerializeField] EncyclopediaRewardEffectTypeComponent _effectLabelHeal;
        [SerializeField] EncyclopediaRewardEffectTypeComponent _effectLabelAttackPower;
        [SerializeField] EncyclopediaRewardEffectTypeComponent _effectLabelHp;
        [SerializeField] GameObject _badgeIcon;
        [SerializeField] GameObject _receivedIcon;
        [SerializeField] Button _button;

        public void Setup(EncyclopediaRewardListCellViewModel viewModel, Action<EncyclopediaRewardListCellViewModel> onSelect)
        {
            _rank.SetText(viewModel.RequireGrade.ToString());
            _rewardIcon.Setup(viewModel.RewardItem);
            _badgeIcon.SetActive(viewModel.Badge.Value);
            _receivedIcon.SetActive(viewModel.IsReceived.Value);
            _button.interactable = !viewModel.IsReceived.Value;
            SetLabel(viewModel.EffectType, viewModel.EffectValue);

            _button.onClick.RemoveAllListeners();
            _button.onClick.AddListener(() => onSelect?.Invoke(viewModel));
        }

        void SetLabel(UnitEncyclopediaEffectType type, UnitEncyclopediaEffectValue value)
        {
            _effectLabelHeal.Hidden = type != UnitEncyclopediaEffectType.Heal;
            _effectLabelAttackPower.Hidden = type != UnitEncyclopediaEffectType.AttackPower;
            _effectLabelHp.Hidden = type != UnitEncyclopediaEffectType.Hp;

            _effectLabelHeal.Setup(value);
            _effectLabelAttackPower.Setup(value);
            _effectLabelHp.Setup(value);
        }
    }
}

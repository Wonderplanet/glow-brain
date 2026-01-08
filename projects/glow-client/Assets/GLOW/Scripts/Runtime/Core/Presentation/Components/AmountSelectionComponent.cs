using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    public class AmountSelectionComponent : UIObject
    {
        [SerializeField] UIText _amountText;
        [SerializeField] Button _countUpButton;
        [SerializeField] Button _countDownButton;
        [SerializeField] Button _countUpBy10Button;
        [SerializeField] Button _countDownBy10Button;
        [SerializeField] Button _maxButton;
        [SerializeField] Button _minButton;

        [SerializeField] UIObject _countUpButtonGrayOutObject;
        [SerializeField] UIObject _countDownButtonGrayOutObject;
        [SerializeField] UIObject _countUpBy10ButtonGrayOutObject;
        [SerializeField] UIObject _countDownBy10ButtonGrayOutObject;
        [SerializeField] UIObject _maxButtonGrayOutObject;
        [SerializeField] UIObject _minButtonGrayOutObject;

        ItemAmount _amount;
        ItemAmount _maxAmount;
        Action _action;

        public ItemAmount Amount => _amount;

        public void Setup(ItemAmount amount, ItemAmount maxAmount, Action action = null)
        {
            _amount = amount;
            _maxAmount = maxAmount;
            _action = action;

            _amountText.SetText(amount.ToString());

            // 初期化
            _countUpButton.onClick.RemoveAllListeners();
            _countDownButton.onClick.RemoveAllListeners();
            _countUpBy10Button.onClick.RemoveAllListeners();
            _countDownBy10Button.onClick.RemoveAllListeners();
            _maxButton.onClick.RemoveAllListeners();

            _countUpButton.onClick.AddListener(() => IncreaseAmount(1));
            _countDownButton.onClick.AddListener(() => DecreaseAmount(1));
            _countUpBy10Button.onClick.AddListener(() => IncreaseAmount(10));
            _countDownBy10Button.onClick.AddListener(() => DecreaseAmount(10));
            _maxButton.onClick.AddListener(ToMax);

            if(_minButton != null)
            {
                _minButton.onClick.RemoveAllListeners();
                _minButton.onClick.AddListener(ToMin);
            }

            ApplyGrayOut();
        }

        void IncreaseAmount(int amount)
        {
            if (_amount >= _maxAmount)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
            }
            else
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }

            _amount += amount;
            _amount = _amount.Clamp(ItemAmount.One, _maxAmount);

            ApplyAmount();
        }

        void DecreaseAmount(int amount)
        {
            if (_amount <= ItemAmount.One)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
            }
            else
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }

            _amount -= amount;
            _amount = _amount.Clamp(ItemAmount.One, _maxAmount);

            ApplyAmount();
        }

        void ToMax()
        {
            if (_amount >= _maxAmount)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
            }
            else
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }

            _amount = _maxAmount;
            ApplyAmount();
        }

        void ToMin()
        {
            if (_amount <= ItemAmount.One)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
            }
            else
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }

            _amount = ItemAmount.One;
            ApplyAmount();
        }

        void ApplyAmount()
        {
            _amountText.SetText(_amount.ToString());

            ApplyGrayOut();

            _action?.Invoke();
        }

        void ApplyGrayOut()
        {
            bool isMax = _amount >= _maxAmount;
            bool isMin = _amount <= ItemAmount.One;

            _countUpButtonGrayOutObject.Hidden = !isMax;
            _countDownButtonGrayOutObject.Hidden = !isMin;
            _countUpBy10ButtonGrayOutObject.Hidden = !isMax;
            _countDownBy10ButtonGrayOutObject.Hidden = !isMin;
            _maxButtonGrayOutObject.Hidden = !isMax;

            if (_minButtonGrayOutObject != null)
            {
                _minButtonGrayOutObject.Hidden = !isMin;
            }
        }
    }
}

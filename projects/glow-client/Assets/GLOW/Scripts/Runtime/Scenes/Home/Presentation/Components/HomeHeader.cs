using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public sealed class HomeHeader : MonoBehaviour
    {
        [Header("アバター")]
        [SerializeField] HomeHeaderAvatarImage _avatarImage;
        [SerializeField] GameObject _avatarBadge;
        // [SerializeField] HomeHeaderAvatarFrame _avatarFrame;
        [SerializeField] UIText _userNameText;
        [Header("エンブレム")]
        [SerializeField] HomeHeaderEmblemImage _emblemImage;
        [SerializeField] GameObject _emblemBadge;
        [Header("レベル")]
        [SerializeField] UIText _levelText;
        [SerializeField] UIText _coinText;
        [SerializeField] HomeHeaderExpGauge _expGauge;
        [SerializeField] AnimationPlayer _levelUpTextAnimation;
        [Header("スタミナ")]
        [SerializeField] UIText _staminaText;
        [SerializeField] UIText _staminaTimeText;
        [SerializeField] UIObject _staminaTimeObject;
        [SerializeField] GameObject _staminaAdvBadge;
        [Header("パス効果のエフェクト(スタミナ)")]
        [SerializeField] UIObject _additionalStaminaPassEffectFull;
        [SerializeField] UIObject _additionalStaminaPassEffectRecovering;
        [Header("石")]
        [SerializeField] UIText _diamondText;
        [SerializeField] UIText _paidDiamondText;
        [Header("ボタン")]
        [SerializeField] Button _staminaRecoverButton;
        [SerializeField] Button _avatarButton;
        [SerializeField] Button _userNameButton;
        [SerializeField] Button _diamondBuyButton;
        [SerializeField] Button _emblemButton;

        public HomeHeaderAvatarImage HomeHeaderAvatarImage => _avatarImage;
        // public HomeHeaderAvatarFrame HomeHeaderAvatarFrame => _avatarImage;
        public HomeHeaderEmblemImage HomeHeaderEmblemImage => _emblemImage;

        public UserName UserNameText { set => _userNameText.SetText(value.Value); }

        public void SetLevel(UserLevel level)
        {
            _levelText.SetText("{0}",level.ToStringAmount());
        }

        public void PlayLevelUpTextAnimation()
        {
            _levelUpTextAnimation.Play();
        }

        public void SetCoin(Coin coin)
        {
            _coinText.SetText(AmountFormatter.FormatAmount(coin.Value));
        }

        public void SetFreeDiamond(FreeDiamond diamond)
        {
            _diamondText.SetText(AmountFormatter.FormatAmount(diamond.Value));
        }
        public void SetPaidDiamond(PaidDiamond diamond)
        {
            _paidDiamondText.SetText(AmountFormatter.FormatAmount(diamond.Value));
        }

        public void SetStamina(HomeHeaderStaminaViewModel viewModel)
        {
            _staminaText.SetText("{0}/{1}", viewModel.Stamina.Value, viewModel.MaxStamina.Value);
            _staminaTimeObject.Hidden = viewModel.RemainFullRecoverySeconds.IsZero();
            _staminaTimeText.SetText(AmountFormatter.FormatSecond(viewModel.RemainUpdatingStaminaRecoverSeconds.ToTimeSpan()));

            SetHoldAdditionalStaminaPassEffect(
                viewModel.IsHeldAdditionalStaminaPassEffect,
                viewModel.RemainFullRecoverySeconds);
        }

        public void SetExp(RelativeUserExp current, RelativeUserExp max)
        {
            _expGauge.SetExpGauge(current, max);
        }

        public async UniTask PlayExpGaugeAnimation(
            CancellationToken cancellationToken,
            UserExpGainViewModel userExpGainViewModel)
        {
            await _expGauge.PlayGaugeAnimation(cancellationToken, 0.2f, userExpGainViewModel);
        }

        public async UniTask PlayLevelUpEffectAsync(CancellationToken cancellationToken, bool isLevelMax)
        {
            await _expGauge.PlayLevelUpEffectAsync(cancellationToken, isLevelMax);
        }

        public void SetStaminaBadge(bool isShow)
        {
            _staminaAdvBadge.SetActive(isShow);
        }
        public void SetAvatarBadge(bool isShow)
        {
            _avatarBadge.SetActive(isShow);
        }
        public void SetEmblemBadge(bool isShow)
        {
            _emblemBadge.SetActive(isShow);
        }

        public void EnableTap()
        {
            _staminaRecoverButton.interactable = true;
            _avatarButton.interactable = true;
            _userNameButton.interactable = true;
            _diamondBuyButton.interactable = true;
            _emblemButton.interactable = true;

        }

        public void DisableTap()
        {
            _staminaRecoverButton.interactable = false;
            _avatarButton.interactable = false;
            _userNameButton.interactable = false;
            _diamondBuyButton.interactable = false;
            _emblemButton.interactable = false;
        }

        void SetHoldAdditionalStaminaPassEffect(
            HeldAdditionalStaminaPassEffectFlag isHeldAdditionalStaminaPassEffect,
            RemainStaminaRecoverSecond remainStaminaRecoverSecond)
        {
            if (isHeldAdditionalStaminaPassEffect)
            {
                _additionalStaminaPassEffectRecovering.Hidden = !remainStaminaRecoverSecond.IsZero();
                _additionalStaminaPassEffectFull.Hidden = remainStaminaRecoverSecond.IsZero();
            }
            else
            {
                _additionalStaminaPassEffectRecovering.Hidden = true;
                _additionalStaminaPassEffectFull.Hidden = true;
            }
        }
    }
}

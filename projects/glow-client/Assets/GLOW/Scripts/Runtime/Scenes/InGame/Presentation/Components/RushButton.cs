using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.InGame.Presentation.Components
{


    public class RushButton : UIObject
    {
        readonly string _gaugeZeroStateName = "051-01_VButton@Gauge_0";
        readonly string _gaugeOneStateName = "051-01_VButton@Gauge_1_in";
        readonly string _gaugeTwoStateName = "051-01_VButton@Gauge_2_in";
        readonly string _gaugeMaxStateName = "051-01_VButton@Max_in";
        readonly string _gaugeMaxLoopStateName = "051-01_VButton@Max_Loop";

        [SerializeField] Button _button;
        [SerializeField] Animator _rootAnimator;
        [SerializeField] Image _chargeGaugeFirstImage;
        [SerializeField] Image _chargeGaugeSecondImage;
        [SerializeField] Image _chargeGaugeThirdImage;
        [SerializeField] UIImage _buttonChargeCountZeroImage;
        [SerializeField] UIImage _notRushImage;
        [SerializeField] UIImage _cancelImage;
        [SerializeField] RushPowerUpIcon _rushPowerUpIcon;

        public void Initialize(RushModel rushModel)
        {
            bool canRush = CanRush(rushModel);
            bool isChargeCountZero = rushModel.ChargeCount.IsZero();

            _button.interactable = canRush;
            _buttonChargeCountZeroImage.Hidden = !isChargeCountZero;
            _notRushImage.Hidden = canRush || isChargeCountZero;
            UpdateChargeGauge(rushModel.ChargeCount, rushModel.ChargeTime, rushModel.RemainingChargeTime);

            _rushPowerUpIcon.UpdateIcon(rushModel.PowerUpStateEffectBonus);
        }

        public void UpdateRushButton(RushModel rushModel)
        {
            // チュートリアル中で非表示にしている場合は早期リターン
            if (Hidden) return;
            
            bool canRush = CanRush(rushModel);
            bool isChargeCountZero = rushModel.ChargeCount.IsZero();

            _button.interactable = canRush || !_cancelImage.Hidden;
            UpdateChargeGauge(rushModel.ChargeCount, rushModel.ChargeTime, rushModel.RemainingChargeTime);

            _buttonChargeCountZeroImage.Hidden = !isChargeCountZero;
            _notRushImage.Hidden = canRush || isChargeCountZero;

            _rushPowerUpIcon.UpdateIcon(rushModel.PowerUpStateEffectBonus);
        }

        public void SetButtonCancel(bool isCancel)
        {
            _cancelImage.Hidden = !isCancel;
        }

        public void OnRushAttackPowerUp(Vector3 iconPos, PercentageM updatedPowerUp)
        {
            _rushPowerUpIcon.OnRushAttackPowerUp(iconPos, updatedPowerUp);
        }

        void UpdateChargeGauge(RushChargeCount count, TickCount chargeTime, TickCount remainingChargeTime)
        {
            float fillAmount = !chargeTime.IsZero() ? 1f - remainingChargeTime / chargeTime : 0f;
            var stateInfo = _rootAnimator.GetCurrentAnimatorStateInfo(0);

            if (count == 0)
            {
                _chargeGaugeFirstImage.fillAmount = fillAmount;
                _chargeGaugeSecondImage.fillAmount = 0f;
                _chargeGaugeThirdImage.fillAmount = 0f;

                if (!stateInfo.IsName(_gaugeZeroStateName))
                {
                    _rootAnimator.Play(_gaugeZeroStateName);
                }
            }
            else if (count == 1)
            {
                _chargeGaugeFirstImage.fillAmount = 1f;
                _chargeGaugeSecondImage.fillAmount = fillAmount;
                _chargeGaugeThirdImage.fillAmount = 0f;

                if (!stateInfo.IsName(_gaugeOneStateName))
                {
                    _rootAnimator.Play(_gaugeOneStateName);
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_038);
                }
            }
            else if (count == 2)
            {
                _chargeGaugeFirstImage.fillAmount = 1f;
                _chargeGaugeSecondImage.fillAmount = 1f;
                _chargeGaugeThirdImage.fillAmount = fillAmount;

                if (!stateInfo.IsName(_gaugeTwoStateName))
                {
                    _rootAnimator.Play(_gaugeTwoStateName);
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_039);
                }
            }
            else // if (count == 3)
            {
                _chargeGaugeFirstImage.fillAmount = 1f;
                _chargeGaugeSecondImage.fillAmount = 1f;
                _chargeGaugeThirdImage.fillAmount = 1f;

                if (!stateInfo.IsName(_gaugeMaxStateName) && !stateInfo.IsName(_gaugeMaxLoopStateName))
                {
                    _rootAnimator.Play(_gaugeMaxStateName);
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_040);
                }
            }
        }

        bool CanRush(RushModel rushModel)
        {
            return rushModel.CanExecuteRushFlag;
        }
    }
}

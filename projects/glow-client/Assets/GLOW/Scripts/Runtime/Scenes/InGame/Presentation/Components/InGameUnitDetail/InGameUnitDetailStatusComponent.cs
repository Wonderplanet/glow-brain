using System;
using System.Collections.Generic;
using System.Linq;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail
{
    public class InGameUnitDetailStatusComponent : UIObject
    {
        [Header("基本ステータス")]
        [SerializeField] UIObject _baseStatus;
        [SerializeField] UIText _hp;
        [SerializeField] UIText _maxHp;
        [SerializeField] UIText _attackPower;
        [SerializeField] UIText _attackRange;
        [SerializeField] UIText _moveSpeed;
        [Header("スペシャルステータス")]
        [SerializeField] UIObject _specialStatus;
        [SerializeField] UIText _rush;

        [Header("ステータス変化矢印")]
        [SerializeField] Animator _attackPowerUpArrow;
        [SerializeField] Animator _attackPowerDownArrow;
        [SerializeField] Animator _moveSpeedUpArrow;
        [SerializeField] Animator _moveSpeedDownArrow;
        [SerializeField] Animator _hpUpArrow;
        [SerializeField] Animator _hpDownArrow;

        [Header("バルーン表示")]
        [SerializeField] CanvasGroup _statusUpBalloon;
        [SerializeField] UIText _statusUpBalloonText;

        public void Setup(InGameUnitDetailStatusViewModel viewModel)
        {
            _baseStatus.Hidden = viewModel.RoleType == CharacterUnitRoleType.Special;
            _specialStatus.Hidden = viewModel.RoleType != CharacterUnitRoleType.Special;

            if (viewModel.IsTutorialIntroductionUnit)
            {
                // 導入チュートリアル中はHPと攻撃力を？？？表示にする
                _hp.SetText("???");
                _maxHp.SetText("???");
                _attackPower.SetText("???");
            }
            else
            {
                _hp.SetText(viewModel.CurrentHp.ToString());
                _maxHp.SetText(viewModel.Hp.ToString());
                _attackPower.SetText(viewModel.AttackPower.ToStringN0());
            }
            
            _attackRange.SetText(viewModel.AttackRange.ToLocalizeString());
            _moveSpeed.SetText(viewModel.MoveSpeed.ToConvertedString());
            
            _rush.SetText("{0}%", viewModel.AttackPower.ToRushPercentageM().ToStringF2());

            // タイムスケールを無視する
            _attackPowerUpArrow.updateMode = AnimatorUpdateMode.UnscaledTime;
            _attackPowerDownArrow.updateMode = AnimatorUpdateMode.UnscaledTime;
            _moveSpeedUpArrow.updateMode = AnimatorUpdateMode.UnscaledTime;
            _moveSpeedDownArrow.updateMode = AnimatorUpdateMode.UnscaledTime;
            _hpUpArrow.updateMode = AnimatorUpdateMode.UnscaledTime;
            _hpDownArrow.updateMode = AnimatorUpdateMode.UnscaledTime;

            SetAttackPowerArrow(viewModel.AttackPower, viewModel.DefaultAttackPower);
            SetMoveSpeedArrow(viewModel.MoveSpeed, viewModel.DefaultMoveSpeed);
            SetHpArrow(viewModel.Hp, viewModel.DefaultHp);

            SetBalloonAnimation(viewModel.InGameUnitDetailBalloonMessageList);
        }

        void SetAttackPowerArrow(AttackPower attackPower, AttackPower defaultAttackPower)
        {
            var isUp = attackPower > defaultAttackPower;
            var isDown = attackPower < defaultAttackPower;
            _attackPowerUpArrow.gameObject.SetActive(isUp);
            _attackPowerDownArrow.gameObject.SetActive(isDown);
        }

        void SetMoveSpeedArrow(UnitMoveSpeed moveSpeed, UnitMoveSpeed defaultMoveSpeed)
        {
            var isUp = moveSpeed > defaultMoveSpeed;
            var isDown = moveSpeed < defaultMoveSpeed;
            _moveSpeedUpArrow.gameObject.SetActive(isUp);
            _moveSpeedDownArrow.gameObject.SetActive(isDown);
        }

        void SetHpArrow(HP hp, HP defaultHp)
        {
            var isUp = hp > defaultHp;
            var isDown = hp < defaultHp;
            _hpUpArrow.gameObject.SetActive(isUp);
            _hpDownArrow.gameObject.SetActive(isDown);
        }

        void SetBalloonAnimation(IReadOnlyList<InGameUnitDetailBalloonMessage> balloonMessages)
        {
            // スペシャルルールのバルーン表示
            _statusUpBalloon.gameObject.SetActive(balloonMessages.Any());

            // アニメーションで表示を切り替える
            if (balloonMessages.Any())
            {
                var messageIndex = 0;
                var messageList = balloonMessages;

                _statusUpBalloonText.SetText(messageList[messageIndex].Value.ToString());

                // FadeInOutアニメーションをループ再生
                DOTween
                    .Sequence()
                    .SetUpdate(true)
                    .Append(_statusUpBalloon.DOFade(0f, 0.7f))
                    .AppendCallback(() =>
                    {
                        messageIndex = (messageIndex + 1) % messageList.Count;
                        _statusUpBalloonText.SetText(messageList[messageIndex].Value.ToString());
                    })
                    .Append(_statusUpBalloon.DOFade(1f, 0.7f))
                    .AppendInterval(0.3f)
                    .SetLoops(-1)
                    .SetLink(gameObject, LinkBehaviour.KillOnDestroy);
            }
        }
    }
}

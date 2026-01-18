using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public class VictoryResultSpeedAttackListCell : UIObject
    {
        [SerializeField] PlayerResourceIconComponent _icon;
        [SerializeField] UIText _time;
        [SerializeField] UIObject _acquiredMark;
        [SerializeField] Animator _clearStampAnimator;

        bool _isNewAcquire;

        public void Setup(
            PlayerResourceIconViewModel icon,
            StageClearTime time,
            AcquiredRewardFlag acquiredFlag,
            NewRewardFlag isNewFlag)
        {
            _icon.Setup(icon);
            _time.SetText("{0}秒以内にクリア", time.ToStringSeconds());
            _acquiredMark.Hidden = !acquiredFlag;
            _isNewAcquire = isNewFlag;
        }

        public void SetClearStamp()
        {
            if (!_isNewAcquire) return;

            _acquiredMark.Hidden = false;
            _clearStampAnimator.Play("Default");
        }

        public async UniTask PlayClearStamp(CancellationToken cancellationToken)
        {
            if (!_isNewAcquire) return;

            SoundEffectPlayer.Play(SoundEffectId.SSE_053_015);

            _acquiredMark.Hidden = false;
            _clearStampAnimator.Play("ClearStamp");
            await UniTask.WaitUntil(() =>
            {
                var info = _clearStampAnimator.GetCurrentAnimatorStateInfo(0);
                return info.normalizedTime >= 1;
            }, cancellationToken: cancellationToken);
        }
    }
}

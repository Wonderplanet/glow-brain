using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.Rush
{
    public class EndRushLayer : MonoBehaviour
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] EndRushResultLayer _endRushResultLayer;

        public Action OnUnpauseSignalAction { get; set; }

        public void SetUpEndRushResultLayer(
            RushChargeCount chargeCount,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType)
        {
            _endRushResultLayer.SetUpRushResult(
                chargeCount,
                calculatedRushAttackPower,
                rushEvaluationType);
        }
        
        public void SetUpEndOpponentRushResultLayer(AttackPower calculatedRushAttackPower)
        {
            _endRushResultLayer.SetUpOpponentRushResult(calculatedRushAttackPower);
        }
        
        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            gameObject.SetActive(true);
            if (_timelineAnimation != null)
            {
                await _timelineAnimation.PlayAsync(cancellationToken);
            }
            gameObject.SetActive(false);
        }

        public void Pause(bool pause)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(pause);
            }
        }

        public void OnUnpauseSignal()
        {
            OnUnpauseSignalAction?.Invoke();
        }
    }
}

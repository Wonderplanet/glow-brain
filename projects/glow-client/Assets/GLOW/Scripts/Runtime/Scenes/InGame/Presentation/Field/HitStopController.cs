using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.InGame.Domain.Constants;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class HitStopController : IDisposable
    {
        float _hitStopDuration;
        bool _isHitStop;
        float _endTime;
        CancellationTokenSource _cancellationTokenSource;

        bool _isPause;
        float _pauseTime;

        public Action OnHitStopStarted { get; set; }
        public Action OnHitStopEnded { get; set; }

        public HitStopController()
        {
            _hitStopDuration = InGameConstants.HitStopDuration.ToSeconds();
        }

        public void Dispose()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }

        public void StartHitStop()
        {
            CancelHitStop();
            
            _cancellationTokenSource = new CancellationTokenSource();

            DoAsync.Invoke(_cancellationTokenSource.Token, async cancellationToken =>
            {
                _isHitStop = true;
                OnHitStopStarted?.Invoke();

                await DelayHitStopDuration(cancellationToken);

                _isHitStop = false;
                OnHitStopEnded?.Invoke();
            });
        }

        public void Pause(bool isPause)
        {
            if (!_isPause && isPause)
            {
                _pauseTime = Time.time;
            }

            if (_isPause && !isPause)
            {
                _endTime += Time.time - _pauseTime;
            }

            _isPause = isPause;
        }

        void CancelHitStop()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;

            if (_isHitStop)
            {
                OnHitStopEnded?.Invoke();
            }
        }

        async UniTask DelayHitStopDuration(CancellationToken cancellationToken)
        {
            _endTime = Time.time + _hitStopDuration;

            while (_isPause || Time.time < _endTime)
            {
                await UniTask.Yield(PlayerLoopTiming.Update, cancellationToken);
            }
        }
    }
}

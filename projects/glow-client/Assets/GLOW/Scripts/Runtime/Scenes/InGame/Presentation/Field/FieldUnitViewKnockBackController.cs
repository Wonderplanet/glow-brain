using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using Spine;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class FieldUnitViewKnockBackController : IDisposable
    {
        const float FirstHopHeight = 0.8f;
        const float SecondHopHeight = 0.3f;
        const float FirstHopTimeBase = 0.4f;
        const float SecondHopTimeBase = 0.2f;

        UnitImage _unitImage;
        Bone _rootBone;
        float _startTime;
        float _hopTime;
        float _hopHeight;

        bool _isPause;
        float _pauseTime;

        CancellationTokenSource _cancellationTokenSource;

        public void StartKnockBack(UnitImage unitImage, TickCount duration)
        {
            _unitImage = unitImage;
            _rootBone = unitImage.RootBone;

            var trackEntry = unitImage.StartAnimation(CharacterUnitAnimation.KnockBack, CharacterUnitAnimation.Empty);

            var nockBackDuration = duration.ToSeconds();
            var animationDuration = trackEntry.Animation.Duration;
            trackEntry.TimeScale = animationDuration / nockBackDuration;

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = new CancellationTokenSource();

            Hop(unitImage, nockBackDuration, _cancellationTokenSource.Token).Forget();
        }

        public void Pause(bool isPause)
        {
            if (isPause)
            {
                _pauseTime = Time.time;
            }

            // ポーズ解除したときジャンプ開始時刻を補正する
            if (_isPause && !isPause)
            {
                _startTime = Time.time - (_pauseTime - _startTime);
            }

            _isPause = isPause;
        }

        public void Cancel()
        {
            _cancellationTokenSource?.Cancel();

            if (_unitImage != null)
            {
                _unitImage.SkeletonAnimation.UpdateLocal -= UpdateRootBonePosition;
                _unitImage = null;
            }

            if (_rootBone != null)
            {
                _rootBone.X = 0f;
                _rootBone.Y = 0f;
                _rootBone = null;
            }
        }

        public void Dispose()
        {
            Cancel();

            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }

        async UniTask Hop(UnitImage unitImage, float knockBackDuration, CancellationToken cancellationToken)
        {
            var scale = unitImage.SkeletonScale.y != 0f ? 1f / unitImage.SkeletonScale.y : 1f;
            var firstHopHeight = FirstHopHeight * scale;
            var secondHopHeight = SecondHopHeight * scale;

            var numberOfHops = 1 + Mathf.RoundToInt(Mathf.Max(knockBackDuration - FirstHopTimeBase, 0) / SecondHopTimeBase);
            var firstHopTime = numberOfHops == 1 ? knockBackDuration : FirstHopTimeBase;
            var secondHopTime = numberOfHops == 1 ? 0 : (knockBackDuration - firstHopTime) / (numberOfHops - 1);

            unitImage.SkeletonAnimation.UpdateLocal += UpdateRootBonePosition;

            for (int i = 0; i < numberOfHops; i++)
            {
                _startTime = Time.time;
                _hopTime = i == 0 ? firstHopTime : secondHopTime;
                _hopHeight = i == 0 ? firstHopHeight : secondHopHeight;

                await UniTask.WaitUntil(() => !_isPause && Time.time - _startTime > _hopTime, cancellationToken: cancellationToken);
            }

            unitImage.SkeletonAnimation.UpdateLocal -= UpdateRootBonePosition;

            _rootBone.X = 0f;
            _rootBone.Y = 0f;
        }

        void UpdateRootBonePosition(ISkeletonAnimation animation)
        {
            if (_isPause) return;
            
            var t = (Time.time - _startTime) / _hopTime;
            var y = Mathf.Abs(Mathf.Sin(t * Mathf.PI)) * _hopHeight;

            if (_rootBone != null)
            {
                _rootBone.X = 0f;
                _rootBone.Y = y;
            }
        }
    }
}

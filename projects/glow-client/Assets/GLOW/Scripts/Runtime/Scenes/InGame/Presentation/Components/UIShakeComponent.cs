using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.MultipleSwitchController;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class UIShakeComponent : MonoBehaviour
    {
        [SerializeField] float _amplitude = 10f;
        [SerializeField] float _speed = 100f;

        readonly MultipleSwitchController _pauseController = new ();
        readonly MultipleSwitchController _shakeController = new ();
        Vector3 _basePosition;
        float _timeOffset;

        void Awake()
        {
            _shakeController.OnStateChanged = OnShakeStateChanged;
        }

        void OnDestroy()
        {
            _pauseController.Dispose();
            _shakeController.Dispose();
        }

        void Update()
        {
            if (_shakeController.IsOn() && !_pauseController.IsOn())
            {
                // パーリンノイズで揺らす
                var noisePos = (Time.time + _timeOffset) * _speed;
                var x = Mathf.PerlinNoise(noisePos, 0) * 2f - 1f;
                var y = Mathf.PerlinNoise(0, noisePos) * 2f - 1f;

                transform.localPosition = _basePosition + new Vector3(x, y, 0) * _amplitude;
            }
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        public MultipleSwitchHandler StartShake()
        {
            return _shakeController.TurnOn();
        }

        public MultipleSwitchHandler StartShake(MultipleSwitchHandler handler)
        {
            return _shakeController.TurnOn(handler);
        }

        public void Shake(float duration)
        {
            ShakeAsync(duration, this.GetCancellationTokenOnDestroy()).Forget();
        }

        async UniTask ShakeAsync(float duration, CancellationToken cancellationToken)
        {
            var handler = _shakeController.TurnOn();
            await UniTask.Delay((int)(duration * 1000), cancellationToken:cancellationToken);
            handler.Dispose();
        }

        void OnShakeStateChanged(bool shake)
        {
            if (shake)
            {
                _basePosition = transform.localPosition;
                _timeOffset = Random.Range(0f, 100f);
            }
            else
            {
                transform.localPosition = _basePosition;
            }
        }
    }
}

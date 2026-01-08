using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.MultipleSwitchController;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.UI.UIEffect
{
    public class BaseUIEffectView : MonoBehaviour
    {
        protected bool _isCompleted;
        readonly MultipleSwitchController _pauseController = new ();
        Action _onCompleted;

        protected virtual void Awake()
        {
            _pauseController.OnStateChanged = OnPause;
        }

        protected virtual void OnDestroy()
        {
            _isCompleted = true;

            _onCompleted?.Invoke();
            _onCompleted = null;

            _pauseController.Dispose();
        }

        public virtual void Destroy()
        {
        }

        public void AddCompletedAction(Action action)
        {
            if (action != null)
            {
                _onCompleted += action;
            }
        }

        public virtual BaseUIEffectView Play()
        {
            return this;
        }

        public virtual async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            try
            {
                Play();

                using var linkedCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken,
                    this.GetCancellationTokenOnDestroy());

                await UniTask.WaitUntil(() => _isCompleted, cancellationToken: linkedCancellationTokenSource.Token);
            }
            catch (OperationCanceledException)
            {
                // 外部から渡されたCancellationTokenがキャンセルされた場合はそのままthrowして非同期処理をキャンセル。
                // ただし、内部でのキャンセル（例えばDestroy()が呼ばれた場合）では正常に終わった扱いにする。
                if (cancellationToken.IsCancellationRequested)
                {
                    throw;
                }
            }
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        protected virtual void OnPause(bool isPaused)
        {
        }
    }
}


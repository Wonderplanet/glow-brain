using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Presentation.TimelineTracks;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class BaseBattleEffectView : MonoBehaviour
    {
        protected bool _isCompleted;
        readonly MultipleSwitchController _pauseController = new ();
        Dictionary<BattleEffectSignal, Action> _signalActions = new();
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

        public virtual BaseBattleEffectView Play()
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

        public virtual BaseBattleEffectView BindOutpost(GameObject bindRoot)
        {
            return this;
        }

        public virtual BaseBattleEffectView BindCharacterUnit(FieldUnitView fieldUnitView)
        {
            return this;
        }

        public virtual BaseBattleEffectView BindSpecialUnit(FieldSpecialUnitView fieldSpecialUnitView)
        {
            return this;
        }

        public virtual BaseBattleEffectView BindCharacterImage(UnitImage unitImage)
        {
            return this;
        }

        public virtual BaseBattleEffectView BindScreenFlashDelegate(IScreenFlashTrackClipDelegate screenFlashDelegate)
        {
            return this;
        }

        public BaseBattleEffectView ChangeParent(Transform parent)
        {
            transform.SetParent(parent, true);
            return this;
        }

        public BaseBattleEffectView Flip(bool isFlip)
        {
            var myTransform = transform;
            var scale = myTransform.localScale;
            myTransform.localScale = new Vector3(isFlip ? -scale.x : scale.x, scale.y, scale.z);
            return this;
        }

        public void RegisterSignalAction(BattleEffectSignal signal, Action action)
        {
            _signalActions[signal] = action;
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        protected virtual void OnPause(bool isPause)
        {
        }

        protected virtual void Complete()
        {
            _isCompleted = true;

            _onCompleted?.Invoke();
            _onCompleted = null;

            if (gameObject != null)
            {
                Destroy(gameObject);
            }
        }

        protected void InvokeSignalAction(BattleEffectSignal signal)
        {
            if (_signalActions.TryGetValue(signal, out var action))
            {
                action?.Invoke();
            }
        }

        protected void InvokeCompletedActions()
        {
            _onCompleted?.Invoke();
            _onCompleted = null;
        }
    }
}

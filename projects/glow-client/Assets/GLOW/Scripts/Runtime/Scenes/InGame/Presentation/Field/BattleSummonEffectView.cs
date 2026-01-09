using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.Playables;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class BattleSummonEffectView : MonoBehaviour
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] AnimationPlayer _animationPlayer;
        [SerializeField] ParticleSystemComponent _particleSystemComponent;
        [SerializeField] string _attachTargetBone;
        [SerializeField] bool _followBoneRotation;
        [SerializeField] float _bossSummonDelaySeconds;

        [SerializeField] Transform _unitContentTransform;

        readonly MultipleSwitchController _pauseController = new ();

        bool _isCalledBossSummonDelayEndMethod;

        public Action OnCompleted { get; set; }
        public Action OnBossSummonDelayEnd{ get; set; } // ノックバックさせるタイミングに利用
        public Transform UnitContentTransform => _unitContentTransform;

        void Awake()
        {
            var myTransform = transform;
            var scale = myTransform.localScale;
            myTransform.localScale = new Vector3(scale.x, scale.y, scale.z * 0.1f);

            _pauseController.OnStateChanged = OnPause;
        }

        void Start()
        {
            DoAsync.Invoke(this, async ct =>
            {
                await UniTask.WaitUntil(() => _bossSummonDelaySeconds < _animationPlayer.ClipPlayable.GetTime() , cancellationToken: ct);
                BossSummonDelayEnd();
            });
        }

        void OnDestroy()
        {
            _pauseController.Dispose();
        }

        public void Destroy()
        {
            OnAnimationCompleted();
        }

        public BattleSummonEffectView Play()
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.OnCompleted = OnAnimationCompleted;
                _timelineAnimation.Play();
            }
            else if (_animationPlayer != null)
            {
                _animationPlayer.OnDone = OnAnimationCompleted;
                _animationPlayer.Play();
            }
            else if (_particleSystemComponent != null)
            {
                _particleSystemComponent.OnStopped = OnAnimationCompleted;
                _particleSystemComponent.Play();
            }

            return this;
        }

        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        void OnPause(bool isPause)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(isPause);
            }
            else if (_animationPlayer != null)
            {
                _animationPlayer.Pause(isPause);
            }
            else if (_particleSystemComponent != null)
            {
                _particleSystemComponent.Pause(isPause);
            }
        }

        void OnAnimationCompleted()
        {
            OnCompleted?.Invoke();
            Destroy(gameObject);
        }

        void BossSummonDelayEnd()
        {
            if (_isCalledBossSummonDelayEndMethod) return;
            _isCalledBossSummonDelayEndMethod = true;
            OnBossSummonDelayEnd?.Invoke();
        }
    }
}

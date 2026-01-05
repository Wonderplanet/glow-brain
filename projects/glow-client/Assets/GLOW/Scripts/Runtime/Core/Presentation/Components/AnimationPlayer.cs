using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.ValueObjects;
using UnityEngine;
using UnityEngine.Animations;
using UnityEngine.Playables;

namespace GLOW.Core.Presentation.Components
{
    [RequireComponent(typeof(Animator))]
    public class AnimationPlayer : MonoBehaviour
    {
        [SerializeField] AnimationClip _animationClip;
        [SerializeField] bool _animatePhysics;
        [SerializeField] bool _playAutomatically;

        Animator _animator;
        PlayableGraph _graph;
        AnimationClipPlayable _clipPlayable;
        bool _isInitialized;
        bool _isDone;
        bool _isPaused;

        public Action OnPlay;
        public Action OnDone;

        public AnimationClip AnimationClip
        {
            set
            {
                _animationClip = value;
                SetOutput();
            }
        }

        public AnimationClipPlayable ClipPlayable => _clipPlayable;
        public AnimationPlayerTime Length => new (_animationClip.length);
        public bool IsPlaying { get { return _graph.IsPlaying(); } }

        void OnEnable()
        {
            Initialize();

            if (_playAutomatically)
            {
                Play();
            }
        }

        void Initialize()
        {
            if (_isInitialized) { return; }

            _animator = GetComponent<Animator>();
            if (_animator.runtimeAnimatorController != null)
            {
                Debug.LogWarning("AnimatorController already exists. Delete AnimatorController on Animator component.");
                _animator.runtimeAnimatorController = null;
            }


            _animator.updateMode = _animatePhysics ? AnimatorUpdateMode.Fixed : AnimatorUpdateMode.Normal;
            _graph = PlayableGraph.Create();
            _graph.SetTimeUpdateMode(DirectorUpdateMode.GameTime);

            SetOutput();

            _isInitialized = true;
        }

        void Update()
        {
            if (_isDone) { return; }
            if (_animationClip.isLooping) { return; }

            _isDone = _clipPlayable.GetTime() >= _animationClip.length;

            _clipPlayable.SetDone(_isDone);

            if (_isDone)
            {
                _graph.Stop();
                OnDone?.Invoke();
            }
        }

        void SetOutput()
        {
            _clipPlayable = AnimationClipPlayable.Create(_graph, _animationClip);
            var output = AnimationPlayableOutput.Create(_graph, "output", _animator);
            output.SetSourcePlayable(_clipPlayable);
        }

        public void Play()
        {
            if (!_graph.IsPlaying())
            {
                _graph.Play();
            }

            OnPlay?.Invoke();
            _isDone = false;
            _isPaused = false;

            if (!_animationClip.isLooping) { _clipPlayable.SetTime(0); }
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            Play();
            await WaitComplete(cancellationToken);
        }

        public void Stop()
        {
            if (!_graph.IsPlaying()) { return; }

            _isDone = true;
            _isPaused = false;

            _clipPlayable.SetDone(_isDone);
            _graph.Stop();
            OnDone?.Invoke();
        }

        public void Pause(bool pause)
        {
            if (_isDone) { return; }
            if (!_isInitialized) { return; }

            if (pause && _graph.IsPlaying())
            {
                _graph.Stop();
                _isPaused = true;
            }
            else if (!pause && _isPaused && !_graph.IsPlaying())
            {
                _graph.Play();
                _isPaused = false;
            }
        }

        public void Seek(AnimationPlayerTime time)
        {
            _clipPlayable.SetTime(Math.Min(time.Value, _animationClip.length));
        }

        public void Skip()
        {
            _animator.fireEvents = false;
            Seek(Length);
        }

        void OnDestroy()
        {
            _graph.Destroy();
        }

        async UniTask WaitComplete(CancellationToken cancellationToken)
        {
            while (_clipPlayable.GetTime() < _animationClip.length)
            {
                await UniTask.Yield(PlayerLoopTiming.LastUpdate, cancellationToken);
            }
        }
    }
}

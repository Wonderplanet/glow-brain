using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;
using WonderPlanet.UniTaskSupporter;
using Object = UnityEngine.Object;

namespace GLOW.Core.Presentation.Components
{
    [RequireComponent(typeof(PlayableDirector))]
    public class TimelineAnimation : MonoBehaviour
    {
        PlayableDirector _director;
        CancellationTokenSource _completeCheckCancellationTokenSource;

        public Action OnCompleted { get; set; }

        void Awake()
        {
            _director = GetComponent<PlayableDirector>();
            CheckComplete();
        }

        public void Play()
        {
            if (_director != null)
            {
                _director.Play();
                CheckComplete();
            }
        }

        public void Play(TimelineAsset timeline)
        {
            if (_director != null)
            {
                _director.Play(timeline);
                CheckComplete();
            }
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            if (_director == null) return;
            
            using var linkedCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                this.GetCancellationTokenOnDestroy(), 
                cancellationToken);
            
            _director.Play();

            try
            {
                await WaitComplete(linkedCancellationTokenSource.Token);
            }
            catch (OperationCanceledException)
            {
                if (_director != null)
                {
                    _director.Stop();
                }
                throw;
            }
        }

        public async UniTask PlayAsync(TimelineAsset timeline, CancellationToken cancellationToken)
        {
            if (_director == null) return;
            
            using var linkedCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                this.GetCancellationTokenOnDestroy(), 
                cancellationToken);
            
            _director.Play(timeline);

            try
            {
                await WaitComplete(linkedCancellationTokenSource.Token);
            }
            catch (OperationCanceledException)
            {
                if (_director != null)
                {
                    _director.Stop();
                }
                throw;
            }
        }

        public void Stop()
        {
            if (_director != null)
            {
                _director.Stop();
            }
        }

        public void Pause(bool pause)
        {
            if (_director == null) return;

            if (pause)
            {
                _director.Pause();
            }
            else
            {
                _director.Resume();
            }
        }

        public void Skip()
        {
            if (_director != null)
            {
                _director.time = _director.duration;
            }
        }

        public void Bind(string trackName, Object bindingObject)
        {
            if (_director == null) return;
            if (!_director.playableAsset.outputs.Any(binding => binding.streamName == trackName)) return;

            var binding = _director.playableAsset.outputs.First(binding => binding.streamName == trackName);
            _director.SetGenericBinding(binding.sourceObject, bindingObject);
        }
        
        public List<SoundEffectId> GetSoundEffectIds()
        {
            var soundEffectIds = new List<SoundEffectId>();

            var director = _director != null ? _director : GetComponent<PlayableDirector>();    // Prefabの状態で取得したい場合に対応
            if (director == null) return soundEffectIds;
            
            foreach (var output in director.playableAsset.outputs)
            {
                if (output.sourceObject is not SoundEffectTrack soundEffectTrack) continue;
                
                foreach (var clip in soundEffectTrack.GetClips())
                {
                    if (clip.asset is not SoundEffectTrackClip soundEffectTrackClip) continue;
                    
                    soundEffectIds.Add(soundEffectTrackClip.SoundEffectId);
                }
            }

            return soundEffectIds;
        }

        void CheckComplete()
        {
            if (_director.extrapolationMode == DirectorWrapMode.Loop) return;

            _completeCheckCancellationTokenSource?.Cancel();
            _completeCheckCancellationTokenSource?.Dispose();
            _completeCheckCancellationTokenSource = new CancellationTokenSource();

            var linkedCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                this.GetCancellationTokenOnDestroy(), _completeCheckCancellationTokenSource.Token).Token;

            DoAsync.Invoke(linkedCancellationToken, async cancellationToken =>
            {
                while (_director.time < _director.duration)
                {
                    await UniTask.Yield(PlayerLoopTiming.LastUpdate, cancellationToken);
                }

                OnCompleted?.Invoke();
            });
        }

        async UniTask WaitComplete(CancellationToken cancellationToken)
        {
            while (_director.time < _director.duration)
            {
                await UniTask.Yield(PlayerLoopTiming.LastUpdate, cancellationToken);
            }
        }
    }
}

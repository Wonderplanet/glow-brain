using System.Collections.Generic;
using Cysharp.Text;
using UnityEngine;
using UnityEngine.Playables;

#if UNITY_EDITOR
using System;
using System.Reflection;
using UnityEditor;
#endif

namespace GLOW.Core.Presentation.Modules.Audio
{
    public class SoundEffectTrackMixerBehaviour : PlayableBehaviour
    {
        public PlayableDirector Director { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            if (Application.isPlaying)
            {
                ProcessFrameForPlaying(playable);
            }
#if UNITY_EDITOR
            else
            {
                ProcessFrameForNoPlaying(playable);
            }
#endif
        }
        
        void ProcessFrameForPlaying(Playable playable)
        {
            var behaviours = GetBehaviours(playable);

            foreach (var behaviour in behaviours)
            {
                var clip = behaviour.Clip;
                var time = Director.time;

                if (time >= clip.start && !behaviour.IsPlayed)
                {
                    SoundEffectPlayer.Play(behaviour.SoundEffectId);
                    behaviour.IsPlayed = true;
                    behaviour.IsPlaying = true;
                }
                
                if (behaviour.IsPlaying && time >= Director.duration)
                {
                    SoundEffectPlayer.Stop(behaviour.SoundEffectId);
                    behaviour.IsPlaying = false;
                }
            }
        }
        
#if UNITY_EDITOR
        void ProcessFrameForNoPlaying(Playable playable)
        {
            var behaviours = GetBehaviours(playable);

            foreach (var behaviour in behaviours)
            {
                var clip = behaviour.Clip;
                var time = Director.time;

                if (time >= clip.start && time < clip.end)
                {
                    if (!behaviour.IsPlaying)
                    {
                        PlayAudioClip(behaviour.SoundEffectId);
                        behaviour.IsPlaying = true;
                    }
                }
                else
                {
                    behaviour.IsPlaying = false;
                }
            }
        }
        
        void PlayAudioClip(SoundEffectId soundEffectId)
        {
            var path = ZString.Format("Assets/GLOW/Audios/Data/SE/{0}.wav", soundEffectId.ToString());
            var audioClip = AssetDatabase.LoadAssetAtPath(path, typeof(AudioClip));
            if (audioClip == null) return;
            
            var unityEditorAssembly = typeof(AudioImporter).Assembly;
            var audioUtilType = unityEditorAssembly.GetType("UnityEditor.AudioUtil");
            var method = audioUtilType.GetMethod(
                "PlayPreviewClip",
                BindingFlags.Static | BindingFlags.Public,
                null,
                new Type[] {typeof(AudioClip), typeof(int), typeof(bool)},
                null
            );
            if (method == null) return;

            method.Invoke(null, new object[] { audioClip, 0, false });
        }
#endif
        
        List<SoundEffectTrackBehaviour> GetBehaviours(Playable playable)
        {
            var behaviours = new List<SoundEffectTrackBehaviour>();

            var inputCount = playable.GetInputCount();
            for (int i = 0; i < inputCount; i++)
            {
                var inputPlayable = (ScriptPlayable<SoundEffectTrackBehaviour>)playable.GetInput(i);
                behaviours.Add(inputPlayable.GetBehaviour());
            }

            return behaviours;
        }
    }
}
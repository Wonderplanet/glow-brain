using Cysharp.Text;
using UnityEngine;
using UnityEngine.Playables;
using UnityEngine.Timeline;

#if UNITY_EDITOR
using UnityEditor;
#endif

namespace GLOW.Core.Presentation.Modules.Audio
{
    public class SoundEffectTrackClip : PlayableAsset, ITimelineClipAsset
    {
        [SerializeField] SoundEffectId _soundEffectId;
        
        public SoundEffectId SoundEffectId => _soundEffectId;
        
        public ClipCaps clipCaps => ClipCaps.None;
        public TimelineClip Clip { get; set; }

#if UNITY_EDITOR
        public override double duration
        {
            get
            {
                var path = ZString.Format("Assets/GLOW/Audios/Data/SE/{0}.wav", SoundEffectId.ToString());
                var audioClip = (AudioClip)AssetDatabase.LoadAssetAtPath(path, typeof(AudioClip));
                if (audioClip == null) return 1;

                return audioClip.length;
            }
        }
#endif
        
        public override Playable CreatePlayable(PlayableGraph graph, GameObject owner)
        {
            var playable = ScriptPlayable<SoundEffectTrackBehaviour>.Create(graph);
            var behaviour = playable.GetBehaviour();

            behaviour.SoundEffectId = _soundEffectId;
            behaviour.Clip = Clip;
            
            return playable;
        }
    }
}
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    [RequireComponent(typeof(Button))]
    public class GlowButtonSound : MonoBehaviour, IPointerClickHandler
    {
        enum SoundAlias
        {
            Decide,
            Select,
            Close,
            None,
            GrayOut
        }

        [SerializeField] SoundAlias _sound = SoundAlias.Decide;
        [SerializeField] SoundAlias _notInteractableSound = SoundAlias.GrayOut;

        Button _button;

        void Awake()
        {
            _button = GetComponent<Button>();
        }

        void IPointerClickHandler.OnPointerClick(PointerEventData eventData)
        {
            var alias = _button.interactable ? _sound : _notInteractableSound;
            var seId = GetSoundEffectId(alias);
            SoundEffectPlayer.Play(seId);
        }

        SoundEffectId GetSoundEffectId(SoundAlias soundAlias)
        {
            return soundAlias switch
            {
                SoundAlias.Decide => SoundEffectId.SSE_000_001,
                SoundAlias.Select => SoundEffectId.SSE_000_002,
                SoundAlias.Close => SoundEffectId.SSE_000_003,
                SoundAlias.GrayOut => SoundEffectId.SSE_000_013,
                SoundAlias.None => SoundEffectId.None,
                _ => SoundEffectId.SSE_000_001
            };
        }
    }
}

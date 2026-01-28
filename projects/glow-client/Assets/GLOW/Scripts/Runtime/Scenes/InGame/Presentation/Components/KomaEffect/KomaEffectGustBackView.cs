using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaEffectGustBackView : KomaEffectView
    {
        [SerializeField] Animator _gustEffectAnimator;
        
        [SerializeField] Transform _gustEffectTransform;
        [SerializeField] UIImage _allTargetGustBackgroundImage;
        [SerializeField] UIImage _toPlayerGustBackgroundImage;
        
        static readonly int Gust = Animator.StringToHash("Gust");

        protected override void Awake()
        {
            base.Awake();
            
            _gustEffectAnimator.SetBool(Gust, false);
        }

        public override void SetUpKomaEffect(IKomaEffectModel komaEffectModel)
        {
            if (komaEffectModel.EffectType != KomaEffectType.Gust) return;
            
            var gustKomaEffectModel = komaEffectModel as GustKomaEffectModel;
            if (gustKomaEffectModel == null) return;
            
            _allTargetGustBackgroundImage.IsVisible = gustKomaEffectModel.TargetSide == KomaEffectTargetSide.All;
            _toPlayerGustBackgroundImage.IsVisible = gustKomaEffectModel.TargetSide != KomaEffectTargetSide.All;
            
            _gustEffectTransform.localScale = gustKomaEffectModel.GustEffectDirection == GustEffectDirection.ToEnemy ? 
                new Vector3(-1, 1, 1) 
                : new Vector3(1, 1, 1);
        }

        public override void UpdateKomaEffect(IKomaEffectModel komaEffectModel)
        {
            if (komaEffectModel.EffectType != KomaEffectType.Gust) return;

            var gustKomaEffectModel = komaEffectModel as GustKomaEffectModel;
            if (gustKomaEffectModel == null) return;

            if (gustKomaEffectModel.RemainingGustInterval.IsZero())
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_075);
            }

            if (!gustKomaEffectModel.RemainingGustDuration.IsZero())
            {
                _gustEffectTransform.localScale = gustKomaEffectModel.GustEffectDirection == GustEffectDirection.ToEnemy 
                    ? new Vector3(-1, 1, 1) 
                    : new Vector3(1, 1, 1);
            }
            
            _gustEffectAnimator.SetBool(Gust, !gustKomaEffectModel.RemainingGustDuration.IsZero());
        }

        protected override void OnPause(bool pause)
        {
            if (_gustEffectAnimator != null)
            {
                _gustEffectAnimator.speed = pause ? 0 : 1;
            }
        }
        
    }
}

using System;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    [RequireComponent(typeof(ParticleSystem))]
    public class ParticleSystemComponent : MonoBehaviour
    {
        ParticleSystem _particleSystem;
        bool _initialized;

        public Action OnStopped { get; set; }

        protected void Awake()
        {
            Initialize();
        }

        void Initialize()
        {
            if (_initialized) return;
            _initialized = true;

            _particleSystem = GetComponent<ParticleSystem>();
        }

        void OnParticleSystemStopped()
        {
            OnStopped?.Invoke();
        }

        public void Play()
        {
            if (!_initialized) Initialize();
            _particleSystem.Play();
        }

        public void Pause(bool pause)
        {
            if (_particleSystem == null) return;

            if (pause)
            {
                _particleSystem.Pause();
            }
            else
            {
                _particleSystem.Play();
            }
        }
    }
}

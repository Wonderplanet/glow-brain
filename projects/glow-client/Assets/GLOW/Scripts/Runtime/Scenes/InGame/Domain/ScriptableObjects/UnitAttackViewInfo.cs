using System;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.ScriptableObjects
{
    [Serializable]
    public class UnitAttackViewInfo
    {
        [SerializeField] GameObject _attackEffect;
        [SerializeField] GameObject _attackEffectMirror;
        [SerializeField] GameObject _attackLastingEffect;
        [SerializeField] GameObject _attackLastingEffectMirror;
        [SerializeField] GameObject _attackStayedLastingEffect;
        [SerializeField] GameObject _attackStayedLastingEffectMirror;
        [SerializeField] GameObject _attackMangaEffect;
        [SerializeField] GameObject _attackMangaEffectMirror;
        [SerializeField] GameObject _cutInPrefab_background;
        [SerializeField] GameObject _cutInPrefab_unitEffect;
        [SerializeField] GameObject _cutInPrefab_front;

        public GameObject AttackEffect => _attackEffect;
        public GameObject AttackEffectMirror => _attackEffectMirror;
        public GameObject AttackLastingEffect => _attackLastingEffect;
        public GameObject AttackLastingEffectMirror => _attackLastingEffectMirror;
        public GameObject AttackStayedLastingEffect => _attackStayedLastingEffect;
        public GameObject AttackStayedLastingEffectMirror => _attackStayedLastingEffectMirror;
        public GameObject AttackMangaEffect => _attackMangaEffect;
        public GameObject AttackMangaEffectMirror => _attackMangaEffectMirror;
        public GameObject CutInPrefab_background => _cutInPrefab_background;
        public GameObject CutInPrefab_unitEffect => _cutInPrefab_unitEffect;
        public GameObject CutInPrefab_front => _cutInPrefab_front;
    }
}

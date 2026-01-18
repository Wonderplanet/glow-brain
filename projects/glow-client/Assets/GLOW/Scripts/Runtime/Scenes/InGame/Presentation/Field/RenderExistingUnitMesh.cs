using System;
using System.Collections.Generic;
using Spine.Unity;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    /// <summary>
    /// SpineのRenderExistingMeshを基にキャラのアウトライン用に調整したもの
    /// </summary>
    [ExecuteAlways]
    [RequireComponent(typeof(MeshRenderer)), RequireComponent(typeof(MeshFilter))]
    public class RenderExistingUnitMesh : MonoBehaviour
    {
        
        [System.Serializable]
        public struct MaterialReplacement {
            // ReSharper disable once InconsistentNaming
            public string OriginalMaterialName;
            // ReSharper disable once InconsistentNaming
            public Material ReplacementMaterial;
        }
        
        [FormerlySerializedAs("referenceRenderer")] [SerializeField] MeshRenderer _referenceRenderer;
        [SerializeField] Material _replacementMaterial;
        [SerializeField] MaterialReplacement[] _replacementMaterialPairs = Array.Empty<MaterialReplacement>();

        bool _updateViaSkeletonCallback;
        MeshFilter _referenceMeshFilter;
        MeshRenderer _ownRenderer;
        MeshFilter _ownMeshFilter;
        Material[] _sharedMaterials = Array.Empty<Material>();
        Dictionary<string, Material> _replacementMaterialDictionary = new ();

#if UNITY_EDITOR
        void Reset () 
        {
            if (_referenceRenderer == null) {
                _referenceRenderer = transform.parent.GetComponentInParent<MeshRenderer>();
                if (!_referenceRenderer) return;
            }

            _replacementMaterial = null;
            
            Material[] parentMaterials = _referenceRenderer.sharedMaterials;
            if (_replacementMaterialPairs.Length != parentMaterials.Length) {
                _replacementMaterialPairs = new MaterialReplacement[parentMaterials.Length];
            }
            for (int i = 0; i < parentMaterials.Length; ++i) {
                _replacementMaterialPairs[i].OriginalMaterialName = parentMaterials[i].name;
                _replacementMaterialPairs[i].ReplacementMaterial = null;
            }
            
            Awake();
            LateUpdate();
        }
#endif

        void Awake () 
        {
            if (_referenceRenderer == null) {
                _referenceRenderer = transform.parent.GetComponentInParent<MeshRenderer>();
            }

            // subscribe to OnMeshAndMaterialsUpdated
            SkeletonAnimation skeletonRenderer = _referenceRenderer.GetComponent<SkeletonAnimation>();
            if (skeletonRenderer) {
                skeletonRenderer.OnMeshAndMaterialsUpdated -= UpdateOnCallback;
                skeletonRenderer.OnMeshAndMaterialsUpdated += UpdateOnCallback;
                _updateViaSkeletonCallback = true;
            }
            _referenceMeshFilter = _referenceRenderer.GetComponent<MeshFilter>();
            _ownRenderer = GetComponent<MeshRenderer>();
            _ownMeshFilter = GetComponent<MeshFilter>();
            
            InitializeMaterialDictionary();
        }

#if UNITY_EDITOR
        // handle disabled scene reload
        void OnEnable () 
        {
            if (Application.isPlaying)
            {
                Awake();
            }
        }
        
        void Update () {
            if (!Application.isPlaying)
            {
                InitializeMaterialDictionary();
            }
        }
#endif

        void LateUpdate () 
        {
#if UNITY_EDITOR
            if (!Application.isPlaying) {
                UpdateMaterials();
                return;
            }
#endif

            if (_updateViaSkeletonCallback) return;
            UpdateMaterials();
        }

        void UpdateOnCallback (SkeletonRenderer r) 
        {
            UpdateMaterials();
        }

        void UpdateMaterials () 
        {
            _ownMeshFilter.sharedMesh = _referenceMeshFilter.sharedMesh;

            Material[] parentMaterials = _referenceRenderer.sharedMaterials;
            if (_sharedMaterials.Length != parentMaterials.Length) {
                _sharedMaterials = new Material[parentMaterials.Length];
            }
            
            for (int i = 0; i < parentMaterials.Length; ++i)
            {
                _sharedMaterials[i] = _replacementMaterialDictionary.GetValueOrDefault(
                    parentMaterials[i].name, 
                    _replacementMaterial);
            }
            
            _ownRenderer.sharedMaterials = _sharedMaterials;
        }

        void InitializeMaterialDictionary()
        {
            _replacementMaterialDictionary.Clear();
            
            for (int i = 0; i < _replacementMaterialPairs.Length; ++i)
            {
                MaterialReplacement entry = _replacementMaterialPairs[i];
                if (entry.ReplacementMaterial == null) continue;
                
                _replacementMaterialDictionary[entry.OriginalMaterialName] = entry.ReplacementMaterial;
            }
        }
    }
}
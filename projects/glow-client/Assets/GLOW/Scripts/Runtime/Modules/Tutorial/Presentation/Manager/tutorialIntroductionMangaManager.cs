using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using UnityEngine.AddressableAssets;

namespace GLOW.Modules.Tutorial.Presentation.Manager
{
    public class TutorialIntroductionMangaManager : MonoBehaviour
    {
        [SerializeField] AssetReferenceGameObject _tutorialIntroductionMangaPrefab;
        
        GameObject _tutorialIntroductionManga;
        
        public async UniTask Load(CancellationToken cancellationToken)
        {
            await _tutorialIntroductionMangaPrefab.LoadAssetAsync<GameObject>().WithCancellation(cancellationToken);
        }
        
        public GameObject Instantiate(Transform parent)
        {
            _tutorialIntroductionManga = Instantiate((GameObject)_tutorialIntroductionMangaPrefab.Asset, parent);
            return _tutorialIntroductionManga;
        }
        
        public void Unload()
        {
            if (_tutorialIntroductionMangaPrefab.IsValid()) _tutorialIntroductionMangaPrefab.ReleaseAsset();
            _tutorialIntroductionManga = null;
        }
        
    }
}
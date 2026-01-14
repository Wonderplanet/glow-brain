using System.Collections.Generic;

namespace WPFramework.UIEditor
{
    public static class UIEditorSoundEffectManifest
    {
        static IUIEditorSoundEffectManifestLoader Loader { get; set; } = new UIEditorSoundEffectAddressableManifestLoader();

        static IReadOnlyCollection<string> _manifest;

        public static void SetLoader(IUIEditorSoundEffectManifestLoader loader)
        {
            Loader = loader;
        }

        public static void ClearCache()
        {
            _manifest = null;
        }

        public static IReadOnlyCollection<string> Get()
        {
            return _manifest ??= Loader.Load();
        }
    }
}

using System.Collections.Generic;

namespace WPFramework.UIEditor
{
    public interface IUIEditorSoundEffectManifestLoader
    {
        IReadOnlyCollection<string> Load();
    }
}

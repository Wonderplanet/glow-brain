using UnityEngine;
using WonderPlanet.StorageSupporter;

namespace WPFramework.Modules.LocalStorage
{
    public static class LocalStorageDeleter
    {
        public static void DeleteAll()
        {
            PlayerPrefs.DeleteAll();

            DirectorySupport.DeleteFilesAndDirectories(Application.persistentDataPath, false);
            DirectorySupport.DeleteFilesAndDirectories(Application.temporaryCachePath, false);
        }

        public static void DeletePlayerPrefs()
        {
            PlayerPrefs.DeleteAll();
        }
    }
}

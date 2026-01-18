using UnityEngine;
using System.Collections;
using System.Collections.Generic;

#if UNITY_EDITOR
using UnityEditor;
using System;
using System.Reflection;
#endif

[ExecuteAlways]
public class SEPlayer : MonoBehaviour
{
    [System.Serializable]
    public class NamedAudio
    {
        public string name;
        public AudioClip clip;

        [Range(0.1f, 3f)]
        public float pitch = 1f;

        [Range(0f, 1f)]
        public float volume = 1f;
    }

    public List<NamedAudio> seList = new List<NamedAudio>();
    public AudioSource audioSourcePrefab;
    public GameObject audioSourceHolder;

    [Min(1)]
    public int maxKeepSources = 5; // AudioSourceを使い回す最大数（追加分は削除対象）

    private Dictionary<string, NamedAudio> seDict = new();
    private List<(string name, AudioSource source)> activeSources = new();

#if UNITY_EDITOR
    private static MethodInfo _playClipMethod;
    private static MethodInfo _stopAllClipsMethod;

    static SEPlayer()
    {
        var audioUtilType = typeof(AudioClip).Assembly.GetType("UnityEditor.AudioUtil");
        if (audioUtilType != null)
        {
            _playClipMethod = audioUtilType.GetMethod("PlayPreviewClip", BindingFlags.Static | BindingFlags.Public, null, new Type[] { typeof(AudioClip), typeof(int), typeof(bool) }, null);
            _stopAllClipsMethod = audioUtilType.GetMethod("StopAllPreviewClips", BindingFlags.Static | BindingFlags.Public);
        }
    }

    private void EditorPlayClip(AudioClip clip)
    {
        EditorStopAllClips();
        if (clip != null && _playClipMethod != null)
            _playClipMethod.Invoke(null, new object[] { clip, 0, false });
    }

    private void EditorStopAllClips()
    {
        if (_stopAllClipsMethod != null)
            _stopAllClipsMethod.Invoke(null, null);
    }
#endif

    void Awake()
    {
        SetupDictionary();

        if (audioSourceHolder == null)
        {
            audioSourceHolder = GameObject.Find("SEAudioPool");
            if (audioSourceHolder == null)
            {
                audioSourceHolder = new GameObject("SEAudioPool");
                audioSourceHolder.hideFlags = HideFlags.HideAndDontSave;
            }
        }
    }

    void OnValidate()
    {
        SetupDictionary();
        foreach (var se in seList)
        {
            if (se.pitch <= 0f) se.pitch = 1f;
            if (se.volume <= 0f) se.volume = 1f;
        }
    }

    private void SetupDictionary()
    {
        seDict.Clear();
        foreach (var se in seList)
        {
            if (!seDict.ContainsKey(se.name) && se.clip != null)
                seDict.Add(se.name, se);
        }
    }

    public void PlaySE(string seName)
    {
        if (!seDict.TryGetValue(seName, out var data))
        {
            Debug.LogWarning($"SE '{seName}' が見つかりません");
            return;
        }

#if UNITY_EDITOR
        if (!Application.isPlaying)
        {
            EditorPlayClip(data.clip);
            return;
        }
#endif

        var src = GetAvailableAudioSource();
        src.clip = data.clip;
        src.pitch = data.pitch;
        src.volume = data.volume;
        src.Play();

        activeSources.Add((seName, src));
        StartCoroutine(RemoveWhenFinished(seName, src));
    }

    public void StopSE(string seName)
    {
#if UNITY_EDITOR
        if (!Application.isPlaying)
        {
            EditorStopAllClips();
            return;
        }
#endif
        var targets = activeSources.FindAll(pair => pair.name == seName);
        foreach (var (name, src) in targets)
        {
            if (src != null)
            {
                src.Stop();
                RemoveAudioSourceIfTemporary(src);
            }
            activeSources.Remove((name, src));
        }
    }

    public void FadeOutSE(string seName, float duration)
    {
        var targets = activeSources.FindAll(pair => pair.name == seName);
        foreach (var (name, src) in targets)
        {
            StartCoroutine(FadeOutAndRemove(name, src, duration));
        }
    }

    public void FadeOutWithParam(string seNameAndDuration)
    {
        var parts = seNameAndDuration.Split(',');
        if (parts.Length != 2)
        {
            Debug.LogWarning($"[SEPlayer] FadeOutWithParam: 引数の形式が不正です → {seNameAndDuration}");
            return;
        }

        string name = parts[0].Trim();
        if (!float.TryParse(parts[1].Trim(), out float duration))
        {
            Debug.LogWarning($"[SEPlayer] FadeOutWithParam: フェード秒数が不正です → {parts[1]}");
            return;
        }

        FadeOutSE(name, duration);
    }

    public void StopAllSE()
    {
#if UNITY_EDITOR
        if (!Application.isPlaying)
        {
            EditorStopAllClips();
            return;
        }
#endif
        foreach (var (_, src) in activeSources)
        {
            if (src != null)
            {
                src.Stop();
                RemoveAudioSourceIfTemporary(src);
            }
        }
        activeSources.Clear();
    }

    private AudioSource GetAvailableAudioSource()
    {
        var existingSources = audioSourceHolder.GetComponents<AudioSource>();
        foreach (var src in existingSources)
        {
            if (!src.isPlaying) return src;
        }

        return CreateNewAudioSource(audioSourceHolder);
    }

    private AudioSource CreateNewAudioSource(GameObject parent)
    {
        var src = parent.AddComponent<AudioSource>();
        src.playOnAwake = false;
        src.spatialBlend = 0f;
        src.dopplerLevel = 0f;
        src.spread = 0f;
        src.rolloffMode = AudioRolloffMode.Linear;
        src.minDistance = 1f;
        src.maxDistance = 500f;
        src.reverbZoneMix = 0f;
        return src;
    }

    private IEnumerator RemoveWhenFinished(string name, AudioSource src)
    {
        yield return new WaitUntil(() => src == null || !src.isPlaying);
        RemoveAudioSourceIfTemporary(src);
        activeSources.Remove((name, src));
    }

    private IEnumerator FadeOutAndRemove(string name, AudioSource src, float duration)
    {
        if (src == null) yield break;

        float startVolume = src.volume;
        float time = 0f;

        while (time < duration)
        {
            time += Time.unscaledDeltaTime;
            float t = Mathf.Clamp01(time / duration);
            src.volume = Mathf.Lerp(startVolume, 0f, t);

            if (src.volume <= 0.001f)
                break;

            yield return null;
        }

        if (src != null)
        {
            src.Stop();
            RemoveAudioSourceIfTemporary(src);
        }

        activeSources.Remove((name, src));
    }

    private void RemoveAudioSourceIfTemporary(AudioSource src)
    {
        if (src == null || audioSourceHolder == null) return;

        var sources = audioSourceHolder.GetComponents<AudioSource>();

        if (sources.Length > maxKeepSources)
        {
            for (int i = maxKeepSources; i < sources.Length; i++)
            {
                if (sources[i] == src)
                {
                    Destroy(src);
                    break;
                }
            }
        }
    }
}

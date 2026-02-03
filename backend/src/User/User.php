<?php

namespace Karolina\User;
use Karolina\Exception;
use Karolina\KarolinaException;
use Karolina\User\Profile;
use Karolina\Network\Root;
use Respect\Validation\Validator as v;

Class User {

    private $email;
    private $phone;
    private $company;
	private $documentNumber;
    protected $id;
    private $firstName = "Unknown";
    private $lastName = "Anonymous";
    private $newPassword = null;
    private $oldPassword = null;
    private $profile;
    private $isAdmin = false;

    private $settings;  use \Karolina\Setting\SettingTrait;
    private $images = array(); use \Karolina\Image\ImageCollectionTrait;

    private $hasher;
    private $hashString;

    private $groupId = 2;
    private $links = array();
    private $tags = array();
    private $skills;
    private $network;
    private $active = true;

    private $happenings = [];

	private $tagsWithType = [];

	
	public function __construct($id, $email, $firstName, $lastName)

	{
	    $this->setEmail($email);
		$this->id = $id;
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$this->setProfile(new Profile());
		$this->setProfileShortDescription('');
		$this->skills = new \Karolina\User\Skills();
        $this->network = new \Karolina\Network\Root(
            new \Karolina\Network\Node('experiments')
            );

		$this->settings = new \Karolina\Setting\SettingsGroup();
		$this->populateMetaIfNeeded();

	}	

	public function populateMetaIfNeeded () {
		
		if (!$this->profile->metaIsSet('contract_kennitala')) {
			$this->profile->setMeta('contract_kennitala', $this->company);
		}
		if (!$this->profile->metaIsSet('personal_kennitala')) {
			$this->profile->setMeta('personal_kennitala', $this->documentNumber);
		}
/*		if (!$this->profile->getMeta('room_number')) {
			$this->profile->setMeta('room_number', $this->phone);
		}
*/
	}

	public function setContactInformation ($contact) {

		if ($contact == null) {
			$this->setSetting('contact_information', null);
			return;
		}

		$contactInformation = new \Karolina\Content\ContactInformation($contact);
		$this->setSetting('contact_information', $contactInformation->toArray());

	}

	public function getContactInformation () {
		if ($this->settingIsSet('contact_information')) {
			return $this->setting('contact_information');
		} else {
			return null;
		}
	}

    public function setGeographicLocation ($location) {

		if ($location === null) {
			$this->setSetting('geographic_location', null);
			return;
		}


		$geographicLocation = new \Karolina\Content\GeographicLocation($location);
        $this->setSetting('geographic_location', $geographicLocation->toArray());

    }

    public function getGeographicLocation () {

        if ($this->settingIsSet('geographic_location')) {
            return $this->setting('geographic_location');

        } else {
            return null;
        }

    }
	
	/**

		NOTE: Profile meta is set by the user, with no validation.
		It has to be treated as such, i.e. arbitrary untrusted data.

	 */


	public function setProfileMeta ($key, $value) {

		$this->profile->setMeta($key, $value);

	}

	public function getProfileMeta ($key) {

		return $this->profile->getMeta($key);

 	}

	public function getAllProfileMeta () {

		return $this->profile->getAllMeta();
		
	}

    public function setHappeningsRegisteredTo (array $happenings) {

        $this->happenings = $happenings;

    }


    public function getHappeningsRegisteredTo () {

        return $this->happenings;

    }

	public function getHappeningIdsRegisteredTo () {

		$ids = array();

		foreach ($this->happenings as $happening) {
			$ids[] = $happening->getId();
		}

		return $ids;

	}

	public function getMyTopics () {

		$happenings = $this->getHappeningsRegisteredTo();		

		$topics = array();

		foreach ($happenings as $happening) {
			$topics[] = $happening->getUUID();
		}

		return $topics;
	}

    public function isHappeningRegisteredTo ($happeningId) {
        if (isset($this->happenings[$happeningId])) {
            return true;
        }
        return false;
    }

    public function disable () {

        $this->active = false;

    }

    public function enable () {

        $this->active = true;

    }

    public function isActive () {

        return $this->active;

    }

    // For network relationships
    public function getNetworkType () {

    	return "users";
    }


	public function setId ($id) {
        $this->id = $id;
    }

	public function setAdminOfTags ($tagIds) {

        $this->setSetting('admin_of_tags', $tagIds);

    }


	public function setNetwork (Root $network) {

		$this->network = $network;
	}


	public function isSame ($user) {

		if ($user and $user->getId() === $this->getId()) {
			return true;
		}

		return false;

	}

	public function setSkills (array $skills) {

		$this->skills->set($skills);

	}

	public function getSkills () {

		return $this->skills->getAll();
	}

    public function setLinks (array $links) {

        $newLinks = array();
        foreach ($links as $link) {

			$metadata = isset($link['metadata']) ? $link['metadata'] : null;

            $newLinks[] = new \Karolina\Content\Link($link['url'], $link['title'], $metadata);

        }

        $this->links = $newLinks;

    }

    public function getLinks () {

        $linkDocs = array();
        foreach ($this->links as $link) {

            $doc = new \Karolina\Content\LinkDocument($link);
            $linkDocs[] = $doc->get();

        }
        return $linkDocs;

    }


    /*
        Tags with type
    */


	public function addTagWithType ($tagId, $type) {

		if (!isset($this->tagsWithType[$tagId]['types']) || !in_array($type, $this->tagsWithType[$tagId]['types'])) {
			$this->tagsWithType[$tagId]['types'][] = $type;
		}

	}

	public function getTagsWithType () {

		return $this->tagsWithType;
		
	}

	public function getTagsWithTypeDocument () {

		$doc = array();
		foreach ($this->tagsWithType as $tagId => $data) {
			$doc[] = array(
				'tag_id' => $tagId,
				'types' => $data['types']
			);
		}
		return $doc;
	}

    /*
        Tags
    */

    public function replaceTags ($tags) {

        $this->tags = array();

        foreach ($tags as $tag) {

            $this->addTag($tag);
        }

    }

    public function replace ($tags) {

        $this->tags = array();

        foreach ($tags as $tag) {

            $this->addTag($tag);
        }

    }

    public function addTag(\Karolina\Tag\Tag $tag) {

        $this->tags[$tag->getId()] = $tag;
    }

    public function removeTag($tagId) {

        unset($this->tags[$tagId]);

    }

    public function getTags () {

        return $this->tags;

    }

    public function getTagsDocument () {

        $doc = array();
        foreach ($this->tags as $tag) {
            $doc[$tag->getId()] = $tag->getLabelDocument();
            $doc[$tag->getId()]['type'] = $tag->getType();
        }

        return $doc;

    }

	public function getGroupId () {

		return $this->groupId;
	}

	public function getHashString () {

		return $this->hashString;

	}

	public function setHashString ($hash) {

		$this->hashString = (string) $hash;

	}

	public function getHashType () {

		return $this->hasher->getHashType();
	}

	public function setHasher (\Karolina\User\Hashers\HasherInterface $hasher) {

		$this->hasher = $hasher;

	}

	public function isAuthentic ($identifier, $password) {

		if ($identifier != $this->getEmail()) {

			throw new Exception('Trying to authenticate a user with the wrong e-mail address.');

		}

		if (!$this->hasher) {

			throw new Exception("Can't authenticate user. No hasher is set.");

		}

		if ($this->hasher->check($password, $this->hashString)) {

			return true;

		} else {

			return false;
		}



	}

	public function updateProfileImage ($imageFile) {

		$imageCollectionData['profile']['filename'] = $imageFile;
		$this->setImageCollectionFromDocument($imageCollectionData);

	}

	public function getProfileImage () {

		return $this->getImage('profile');

	}

	public function getUserType () {
        if ($this->isAdmin()) {
            return "admin";
        } else {
            return "member";
        }
    }

	public function isAdmin () {

		return $this->isAdmin;

	}

	public function makeAdmin () {

		$this->isAdmin = true;
		$this->groupId = 1;

	}

	public function setProfile (Profile $profile) {

		$this->profile = $profile;

	}

	public function setProfileDescription ($content) {

		$this->profile->setDescription($content);

	}

	public function setProfileShortDescription ($content) {

		$this->profile->setShortDescription($content);

	}


	public function getProfileDescription () {

		return $this->profile->getDescription();


	}

	public function getProfileShortDescription () {

		return $this->profile->getShortDescription();

	}


	public function setFirstName ($name) {

		if (v::stringType()->length(1, 500)->validate($name)) {

			$this->firstName = $name;

		} else {

			throw new KarolinaException(
				'First name must be at least 1 characters',
				500,
				null,
				"invalid_arguments"
			);

		}

	}

	public function setLastName ($name) {

		if (v::stringType()->length(1, 500)->validate($name)) {

			$this->lastName = (string) $name;

		} else {

			throw new KarolinaException(
				'Last name must be at least 1 characters',
				500,
				null,
				"invalid_arguments"
			);

		}

	}

	public function setNewPassword ($password) {

		// Set hasher
		$this->setHasher(new \Karolina\User\Hashers\NativeHasher());

		if (v::stringType()->length(6, 1260)->validate($password)) {

			$this->newPassword = (string) $password;

			$this->setHashString(
				$this->hasher->hash($password)
				);

		} else {

			throw new KarolinaException(
				'Password must be at least 6 characters',
				500,
				null,
				"password_too_short"
			);

		}

	}

	public function setEmail ($email) {

		if (v::email()->validate($email)) {

			$this->email = (string) $email;

		} else {

			throw new KarolinaException(
				'E-mail address is not valid.',
				500,
				null,
				"invalid_email"
			);

		}

	}

    public function setPhone ($phone) {

		$this->phone = (string) $phone;

	}

    public function setCompany ($company) {

		$this->company = (string) $company;

	}


    public function setDocumentNumber ($documentNumber) {

		$this->documentNumber = (string) $documentNumber;

	}

	public function getNewPassword () {

		return $this->newPassword;

	}

	public function getOldPassword () {

		return $this->oldPassword;

	}

	public function setOldPassword ($password) {

		$this->oldPassword = $password;

	}


	public function getEmail () {

	    return $this->email;

	}

	public function getPhone () {

	    return $this->phone;

	}


	public function getCompany () {

	    return $this->company;

	}	

	public function getDocumentNumber () {

	    return $this->documentNumber;

	}

	public function getId () {

	    return $this->id;

	}

	public function getName () {

		return $this->firstName." ".$this->lastName;

	}

	public function getFirstName () {

		return $this->firstName;

	}

	public function getLastName () {

		return $this->lastName;

	}


}
